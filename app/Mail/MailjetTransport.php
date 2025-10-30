<?php

declare(strict_types=1);

namespace App\Mail;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Part\DataPart;

class MailjetTransport extends AbstractTransport
{
    private ClientInterface $client;
    private string $apiKey;
    private string $apiSecret;
    private string $endpoint;
    private bool $sandbox;
    private ?Address $defaultFrom;

    public function __construct(
        ClientInterface $client,
        string $apiKey,
        string $apiSecret,
        string $endpoint = 'https://api.mailjet.com/v3.1/send',
        bool $sandbox = false,
        ?Address $defaultFrom = null,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($dispatcher, $logger);
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->endpoint = $endpoint;
        $this->sandbox = $sandbox;
        $this->defaultFrom = $defaultFrom;
    }

    public function __toString(): string
    {
        return 'mailjet';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $payload = $this->buildPayload($email);

        try {
            $response = $this->client->request('POST', $this->endpoint, [
                'auth' => [$this->apiKey, $this->apiSecret],
                'json' => ['Messages' => [$payload]],
            ]);
        } catch (GuzzleException $exception) {
            throw new TransportException('Mailjet API request failed: ' . $exception->getMessage(), 0, $exception);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new TransportException(sprintf('Mailjet API responded with status %d: %s', $status, $body), $status);
        }

        $decoded = json_decode($body, true);
        $messageStatus = $decoded['Messages'][0]['Status'] ?? null;
        if (strtolower((string) $messageStatus) !== 'success') {
            $error = $decoded['Messages'][0]['Errors'][0]['ErrorMessage'] ?? $body;
            throw new TransportException('Mailjet did not accept the message: ' . $error);
        }
    }

    /**
     * @throws TransportException
     */
    private function buildPayload(Email $email): array
    {
        $from = $this->resolveFrom($email);

        if ($from === null) {
            throw new TransportException('Mailjet transport requires a "from" address.');
        }

        $payload = [
            'From' => $this->formatAddress($from),
            'To' => $this->formatAddresses($email->getTo()),
        ];

        if (! empty($email->getCc())) {
            $payload['Cc'] = $this->formatAddresses($email->getCc());
        }
        if (! empty($email->getBcc())) {
            $payload['Bcc'] = $this->formatAddresses($email->getBcc());
        }

        if ($email->getReplyTo()) {
            $payload['ReplyTo'] = $this->formatAddress($email->getReplyTo()[0]);
        }

        if ($email->getSubject()) {
            $payload['Subject'] = $email->getSubject();
        }

        $text = $email->getTextBody();
        $html = $email->getHtmlBody();

        if ($text !== null) {
            $payload['TextPart'] = $text;
        }
        if ($html !== null) {
            $payload['HTMLPart'] = $html;
        }

        $attachments = $this->buildAttachments($email);
        if ($attachments !== []) {
            $payload = array_merge($payload, $attachments);
        }

        $headers = $this->extractHeaders($email);
        if ($headers !== []) {
            $payload['Headers'] = $headers;
        }

        if ($this->sandbox) {
            $payload['SandboxMode'] = true;
        }

        return $payload;
    }

    private function resolveFrom(Email $email): ?Address
    {
        $from = $email->getFrom();
        if (! empty($from)) {
            return $from[0];
        }

        return $this->defaultFrom;
    }

    /**
     * @param Address[] $addresses
     */
    private function formatAddresses(array $addresses): array
    {
        return array_map(fn (Address $address) => $this->formatAddress($address), $addresses);
    }

    private function formatAddress(Address $address): array
    {
        $result = ['Email' => $address->getAddress()];
        if ($address->getName() !== '') {
            $result['Name'] = $address->getName();
        }

        return $result;
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    private function buildAttachments(Email $email): array
    {
        $regularAttachments = [];
        $inlineAttachments = [];

        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();
            $disposition = null;
            $dispositionHeader = $headers->get('Content-Disposition');
            if ($dispositionHeader) {
                $disposition = strtolower((string) $dispositionHeader->getValue());
            }

            $payload = [
                'ContentType' => $attachment->getContentType(),
                'Filename' => $headers->getHeaderParameter('Content-Disposition', 'filename') ?? $attachment->getName(),
                'Base64Content' => base64_encode($attachment->getBody()),
            ];

            if ($disposition === 'inline') {
                if ($attachment instanceof DataPart && $attachment->getContentId()) {
                    $payload['ContentID'] = $attachment->getContentId();
                }
                $inlineAttachments[] = $payload;
            } else {
                $regularAttachments[] = $payload;
            }
        }

        $result = [];
        if ($regularAttachments !== []) {
            $result['Attachments'] = $regularAttachments;
        }
        if ($inlineAttachments !== []) {
            $result['InlinedAttachments'] = $inlineAttachments;
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function extractHeaders(Email $email): array
    {
        $headers = [];

        foreach ($email->getHeaders()->all() as $header) {
            $name = $header->getName();

            // Skip headers Mailjet manages internally.
            if (in_array(strtolower($name), ['from', 'to', 'cc', 'bcc', 'subject'], true)) {
                continue;
            }

            $headers[$name] = $header->getBodyAsString();
        }

        return $headers;
    }
}

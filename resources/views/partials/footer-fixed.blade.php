@php
    $defaults = [
        'app' => config('app.name', 'PowerBIDash'),
        'year' => date('Y'),
    ];
    $tokens = array_merge($defaults, $replacements ?? []);
    $rawFooter = isset($text) && $text !== null ? trim($text) : '';
    $mappedText = $rawFooter !== ''
        ? strtr($rawFooter, [
            ':app' => $tokens['app'],
            ':year' => $tokens['year'],
        ])
        : null;
@endphp

@if($mappedText !== null)
    {{ $mappedText }}
@else
    {{ __($text_key ?? 'footer.signature', $tokens) }}
@endif

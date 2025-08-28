<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Lista de proxies confiables. Usa '*' si estás detrás de balanceadores conocidos.
     * Si no, déjalo en null y configura a nivel de servidor.
     */
    protected $proxies = '*';

    /**
     * Encabezados usados para detectar IP/Proto originales.
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_FORWARDED;
}

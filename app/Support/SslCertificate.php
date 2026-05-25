<?php

namespace App\Support;

use GuzzleHttp\Client;

class SslCertificate
{
    public static function bundlePath(): ?string
    {
        $configured = env('SSL_CA_BUNDLE');
        if ($configured && is_file($configured)) {
            return $configured;
        }

        $projectPath = storage_path('certs/cacert.pem');
        if (is_file($projectPath)) {
            return $projectPath;
        }

        return null;
    }

    /** @return array<string, mixed> */
    public static function guzzleOptions(): array
    {
        $bundle = self::bundlePath();

        return $bundle ? ['verify' => $bundle] : [];
    }

    public static function httpClient(): Client
    {
        return new Client(self::guzzleOptions());
    }
}

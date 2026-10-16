<?php

namespace Pteroca\PterodactylAddon\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $secret;

    public function __construct()
    {
        $this->secret = (string) env('PTEROCA_SSO_SECRET', '');
    }

    public function decodeToken(?string $token): ?object
    {
        if (empty($token) || $this->secret === '') {
            return null;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));

            // Tokens issued by PteroCA always carry a short expiry; refuse tokens without one.
            if (empty($decoded->exp)) {
                return null;
            }

            return $decoded;
        } catch (\Throwable $exception) {
            return null;
        }
    }
}

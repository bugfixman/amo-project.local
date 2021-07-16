<?php

namespace App\Service;

use Illuminate\Support\Facades\Cache;
use League\OAuth2\Client\Token\AccessToken;
use App\Exceptions\InvalidTokenException;
use App\Exceptions\TokenNotFoundException;

class TokenService
{
    public static function saveToken(array $accessToken) : void
    {
        if (!self::isValidAccessToken($accessToken)) {
            throw new InvalidTokenException;
        }

        Cache::forever('token', json_encode([
            'accessToken' => $accessToken['accessToken'],
            'expires' => $accessToken['expires'],
            'refreshToken' => $accessToken['refreshToken'],
            'baseDomain' => $accessToken['baseDomain'],
        ]));
    }

    public static function getToken() : AccessToken
    {
        if (!Cache::has('token')) {
            throw new TokenNotFoundException;
        }

        $accessToken = json_decode(Cache::get('token'), true);

        if (!self::isValidAccessToken($accessToken)) {
            throw new InvalidTokenException;
        }

        return new AccessToken([
            'access_token' => $accessToken['accessToken'],
            'refresh_token' => $accessToken['refreshToken'],
            'expires' => $accessToken['expires'],
            'baseDomain' => $accessToken['baseDomain'],
        ]);
    }

    private static function isValidAccessToken(array $accessToken) : bool
    {
        if (
            isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            return true;
        }

        return false;
    }
}
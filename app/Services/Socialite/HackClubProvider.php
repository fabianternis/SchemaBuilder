<?php

namespace App\Services\Socialite;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

/**
 * Hack Club Auth — OAuth 2.0 provider for Laravel Socialite.
 *
 * Authorization endpoint : https://auth.hackclub.com/oauth/authorize
 * Token endpoint         : https://auth.hackclub.com/oauth/token
 * User-info endpoint     : https://auth.hackclub.com/api/v1/me
 */
class HackClubProvider extends AbstractProvider
{
    /** @var string[] */
    protected $scopes = ['profile'];

    /** @var string */
    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://auth.hackclub.com/oauth/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://auth.hackclub.com/oauth/token';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get('https://auth.hackclub.com/api/v1/me', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id'       => $user['id'] ?? null,
            'nickname' => $user['username'] ?? $user['slack_id'] ?? null,
            'name'     => $user['name'] ?? null,
            'email'    => $user['email'] ?? null,
            'avatar'   => $user['avatar'] ?? null,
        ]);
    }
}

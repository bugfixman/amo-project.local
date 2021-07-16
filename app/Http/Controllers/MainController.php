<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use League\OAuth2\Client\Token\AccessToken;
use Exception;
use Illuminate\Support\Facades\Cache;

class MainController extends Controller
{
    private $clientApi;

    public function __construct()
    {
        $this->clientApi = new AmoCRMApiClient(
            env('AMO_CLIENT_ID'), 
            env('AMO_SECRET_KEY'),
            env('AMO_REDIRECT_URL')
        );
    }

    public function auth(Request $request)
    {
        if ($request->referer) {
            $this->clientApi->setAccountBaseDomain($request->referer);
        }

        if (!$request->code) {
            $state = bin2hex(random_bytes(16));
            $request->session()->put('authState', $state);

            $btn = $this->clientApi->getOAuthClient()->getOAuthButton(
                [
                    'title' => 'Установить интеграцию',
                    'compact' => true,
                    'class_name' => 'className',
                    'color' => 'default',
                    'error_callback' => 'handleOauthError',
                    'state' => $state,
                ]
            );

            return response($btn);
        }

        $authSessionState = $request->session()->get('authState');

        if ((empty($request->state) || empty($authSessionState)) || ($request->state !== $authSessionState)) {
            $request->session()->forget('authState');
            return response('Invalid state', 422);
        }

        try {
            $accessToken = $this->clientApi->getOAuthClient()->getAccessTokenByCode($request->code);

            if (!$accessToken->hasExpired()) {
                $this->saveToken([
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $this->clientApi->getAccountBaseDomain(),
                ]);
            }
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        $ownerDetails = $this->clientApi->getOAuthClient()->getResourceOwner($accessToken);
        var_dump($ownerDetails);
    }

    /**
     * @param array $accessToken
     */
    private function saveToken($accessToken)
    {
        if (
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            $data = [
                'accessToken' => $accessToken['accessToken'],
                'expires' => $accessToken['expires'],
                'refreshToken' => $accessToken['refreshToken'],
                'baseDomain' => $accessToken['baseDomain'],
            ];

            Cache::forever('token', json_encode($data));
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    /**
     * @return AccessToken
     */
    private function getToken()
    {
        if (!file_exists(TOKEN_FILE)) {
            exit('Access token file not found');
        }

        $accessToken = json_decode(Cache::get('token'), true);

        if (
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            return new AccessToken([
                'access_token' => $accessToken['accessToken'],
                'refresh_token' => $accessToken['refreshToken'],
                'expires' => $accessToken['expires'],
                'baseDomain' => $accessToken['baseDomain'],
            ]);
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }
}

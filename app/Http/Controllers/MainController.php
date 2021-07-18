<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use App\Service\TokenService;

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

        if (!TokenService::getToken()->hasExpired()) {
            return redirect('/');
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
                TokenService::saveToken([
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $this->clientApi->getAccountBaseDomain(),
                ]);

                return redirect('/');
            }
        } catch (AmoCRMoAuthApiException $e) {
            return response($e->getMessage(), 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\TokenService;
use App\Service\LeadService;
use AmoCRM\Client\AmoCRMApiClient;
use League\OAuth2\Client\Token\AccessTokenInterface;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->clientApi = new AmoCRMApiClient(
            env('AMO_CLIENT_ID'),
            env('AMO_SECRET_KEY'),
            env('AMO_REDIRECT_URL')
        );

        $accessToken = TokenService::getToken();

        $this->clientApi->setAccessToken($accessToken)
            ->setAccountBaseDomain($accessToken->getValues()['baseDomain'])
            ->onAccessTokenRefresh(
            function (AccessTokenInterface $accessToken, string $baseDomain) {
                TokenService::saveToken([
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $baseDomain,
                ]);
            }
        );
    }

    public function createLead(Request $request)
    {
        $data = [
            'name' => 'Lead name',
            'contact' => [
                'first_name' => 'Ivan',
                'last_name' => 'Zinoviev',
                'phone' => '+79129876543',
            ],
            'company' => [
                'name' => 'Qwerty LLC',
            ],
            'tag' => 'Новый клиент',
            //'external_id' => '0752a617-c834-4bde-b4a6-76ff0fe26871'
        ];

        var_dump((new LeadService($this->clientApi))->create($data));
    }
}

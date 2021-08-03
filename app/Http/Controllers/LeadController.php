<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\TokenService;
use App\Service\LeadService;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Illuminate\Support\Facades\Validator;

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

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'company_name' => 'required',
            'tag' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false, 
                'error_msg' => $validator->errors()
            ], 422);
        }

        try {
            $addedLead = (new LeadService($this->clientApi))->create([
                'name' => $request->name,
                'contact' => [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone
                ],
                'company' => [
                    'name' => $request->company_name
                ],
                'tag' => $request->tag
            ]);

            return response([
                'success' => true, 
                'lead_id' => $addedLead[0]->getId()
            ], 200);
        } catch (AmoCRMApiException $e) {
            return response([
                'success' => false, 
                'error_msg' => $e->getMessage()
            ], 500);
        }
    }

    public function listLead()
    {
        try {
            return view('leads', [
                'leads' => $this->clientApi->leads()->get()
            ]);
        } catch (AmoCRMApiNoContentException $e) {
            return view('leads', ['leads' => []]);
        }
    }
}

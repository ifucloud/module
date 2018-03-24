<?php
/**
 * Created by IntelliJ IDEA.
 * User: zfm
 * Date: 2018/2/1
 * Time: 下午3:08
 */

namespace Ifucloud\Module\Helpers;

use Exception;
use Ifucloud\Module\Entities\Service;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;

trait OAuthToken
{

    /**
     * @return bool
     * @throws Exception
     */
    public static function serviceValidator()
    {
        $token_type = self::tokenType();

        if ($token_type != 'service') {
            throw new Exception('service not auth', 401);
        } else {
            return true;
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function applicationValidator()
    {
        $token_type = self::tokenType();

        if ($token_type != 'application'
            && $token_type != 'customer'
            && $token_type != 'business') {
            throw new Exception('application not auth', 401);
        } else {
            return true;
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function loginValidator()
    {
        $token_type = self::tokenType();

        if ($token_type != 'customer' && $token_type != 'business') {
            throw new Exception('login not auth', 401);
        } else {
            return true;
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function customerValidator()
    {
        $token_type = self::tokenType();

        if ($token_type != 'customer') {
            throw new Exception('customer not auth', 401);
        } else {
            return true;
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function businessValidator()
    {
        $token_type = self::tokenType();

        if ($token_type != 'business') {
            throw new Exception('business not auth', 401);
        } else {
            return true;
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function allowServiceValidator()
    {
        $service = Service::where('ser_state', '=', '1')->first();

        $token = self::authorization();

        $redis = Redis::connection('token');

        $auth_token = json_decode($redis->get($token));

        if (!$auth_token || !$auth_token->allow_services) {
            throw new Exception('service is not allow', 404);
        } else if ($auth_token->allow_services[0] == '*') {
            return true;
        } else if (!in_array($service->ser_code, $auth_token->allow_services)) {
            throw new Exception('service is not allow', 404);
        } else {
            return true;
        }
    }

    /**
     * @param $method
     * @param $type_code
     * @return mixed
     * @throws Exception
     */
    public static function permissionsValidator($method, $type_code)
    {
        $token = self::authorization();

        $redis = Redis::connection('token');

        $auth_token = json_decode($redis->get($token), true);

        if (!$auth_token) {
            throw new Exception('permissions not allow', 404);
        }

        $permissions = $auth_token['token_permissions'][$method];

        if ($permissions[0] == '*') {
            return true;
        } else if (!in_array($type_code, $permissions)) {
            throw new Exception('permissions not allow', 404);
        } else {
            return true;
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function tokenType()
    {
        $token = self::authorization();

        $redis = Redis::connection('token');

        $auth_token = json_decode($redis->get($token));

        if (!$auth_token || !$auth_token->token_type) {
            throw new Exception('token type not exists', 404);
        } else {
            return $auth_token->token_type;
        }

    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function tokenAccount()
    {
        $token = self::authorization();

        $redis = Redis::connection('token');

        $auth_token = json_decode($redis->get($token));

        if (!$auth_token || !$auth_token->token_account) {
            throw new Exception('token account not exists', 404);
        } else {
            return $auth_token->token_account;
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function tokenUsername()
    {
        $token = self::authorization();

        $redis = Redis::connection('token');

        $auth_token = json_decode($redis->get($token));

        if (!$auth_token || !$auth_token->token_username) {
            throw new Exception('token account not exists', 404);
        } else {
            return $auth_token->token_username;
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function tokenRoles()
    {
        $token = self::authorization();

        $redis = Redis::connection('token');

        $auth_token = json_decode($redis->get($token));

        if (!$auth_token || !$auth_token->token_roles) {
            throw new Exception('roles not exists', 404);
        } else {
            return $auth_token->token_roles;
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function tokenPermissions()
    {
        $token = self::authorization();

        $redis = Redis::connection('token');

        $auth_token = json_decode($redis->get($token));

        if (!$auth_token || !$auth_token->permissions) {
            throw new Exception('permissions not exists', 404);
        } else {
            return json_decode($auth_token->permissions);
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function appId()
    {
        self::applicationValidator();
        $token = self::authorization();

        $redis = Redis::connection('token');

        $auth_token = json_decode($redis->get($token));

        if (!$auth_token || !$auth_token->owner_id) {
            throw new Exception('application not exists', 404);
        } else {
            return $auth_token->owner_id;
        }
    }

    /**
     * @return array|string
     * @throws Exception
     */
    public static function authorization()
    {
        if (!$authorization = request()->header('Authorization')) {
            if (!$token = request()->get('token')) {
                throw new Exception('authorization or token not exists', 404);
            } else {
                return $token;
            }
        } else {
            return $authorization;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function token()
    {
        if (!$token = request()->get('token')) {
            throw new Exception('token not exists', 404);
        } else {
            return $token;
        }
    }

    /**
     * get a service token
     * @return bool
     */
    public static function serviceToken()
    {
        $service_token = self::localServiceToken();

        if (!$service_token || !self::authServiceToken($service_token)) {
            $service_token = self::getNewServiceAuthToken();
        }
        return $service_token;
    }

    /**
     * get local service token
     * @return bool
     */
    public static function localServiceToken()
    {
        $service = Service::where('ser_state', '=', '1')->first();

        if (!$service) return false;

        return $service->oauth_token;
    }

    /**
     * auth service token is right
     * @param $token
     * @return bool
     */
    public static function authServiceToken($token)
    {
        $client = new Client(['base_uri' => config('services.hosts.oauth')]);

        $response = $client->request('GET', 'api/oauth/auth',[
            'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
            'query' => ['token' => $token]
        ]);

        if ($response->getStatusCode() != 200) return false;

        $body = \GuzzleHttp\json_decode($response->getBody());

        if (isset($body->error)) return false;

        return true;
    }

    /**
     * get a new service auth token
     * @return bool
     */
    public static function getNewServiceAuthToken()
    {
        $service = Service::where('ser_state', '=', '1')->first();

        if (!$service) return false;

        $client = new Client(['base_uri' => config('services.hosts.oauth')]);

        $response = $client->request('POST', 'api/service/auth',[
            'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
            'json' => [
                'ser_code'   => $service->ser_code,
                'ser_secret' => $service->ser_secret,
            ]
        ]);

        if ($response->getStatusCode() != 200) return false;

        $body = \GuzzleHttp\json_decode($response->getBody());

        if (isset($body->error)) return false;

        $oauth_token = $body->data->oauth_token;

        Service::where('ser_state', '=', '1')->update(['oauth_token' => $oauth_token]);

        return $oauth_token;
    }
}

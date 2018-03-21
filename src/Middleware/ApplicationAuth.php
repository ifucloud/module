<?php

namespace Ifucloud\Module\Middleware;

use Closure;
use Exception;
use GuzzleHttp\Client;
use Ifucloud\Module\Helpers\OAuthToken;
use Illuminate\Support\Facades\Redis;

class ApplicationAuth
{
    use OAuthToken;

    /**
     * Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {

        try {
            $token = $this->authorizationOrToken();
            $redis = Redis::connection('token');

            if ($redis->exists($token)) return $next($request);
            $client = new Client(['base_uri' => config('services.hosts.oauth')]);

            $response = $client->request('GET', 'api/oauth/auth',[
                'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
                'query' => ['token' => $token]
            ]);

            if ($response->getStatusCode() != 200) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Not Authorization'
                ], 401);
            }

            $body = \GuzzleHttp\json_decode($response->getBody());

            if (isset($body->error)) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Not Authorization'
                ], 401);
            }

            $oauth_token = $body->data;
            $redis->set($token, json_encode($oauth_token));
            $redis->expire($token, 86400*7);

            return $next($request);

        }  catch (Exception $e) {

            return response()->json([
                'error'   => true,
                'message' => $e->getMessage()
            ], 401);
        }
    }

}

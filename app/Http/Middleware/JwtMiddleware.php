<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JwtMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->bearerToken();
        
        if(!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'status' => 'error',
                'code' => 'token-not-provided',
                'message' => 'Token not provided.'
            ], 200);
        }

        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch(ExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'token-expired',
                'message' => __('Provided token is expired.')
            ], 200);
        } catch(\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'error-decoding-token',
                'message' => __('An error while decoding token.')
            ], 200);
        }

        $user = new User();
        $user = $user->find($credentials->sub);

        // Now let's put the user in the request class so that you can grab it from there
        $request->auth = $user;

        return $next($request);
    }
}
?>
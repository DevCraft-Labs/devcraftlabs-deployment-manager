<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->bearerToken();

        if (!$header) {
            return new JsonResponse(['message' => 'Missing bearer token.'], 401);
        }

        $token = ApiToken::query()->where('is_active', true)->get()->first(function (ApiToken $apiToken) use ($header): bool {
            return Hash::check($header, $apiToken->token_hash);
        });

        if (!$token || ($token->expires_at && $token->expires_at->isPast())) {
            return new JsonResponse(['message' => 'Invalid token.'], 401);
        }

        $token->update(['last_used_at' => now()]);

        return $next($request);
    }
}

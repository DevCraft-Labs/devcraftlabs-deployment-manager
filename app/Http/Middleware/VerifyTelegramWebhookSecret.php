<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTelegramWebhookSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $incoming = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');
        $expected = (string) config('services.telegram.webhook_secret', '');

        if ($expected === '' || !hash_equals($expected, $incoming)) {
            abort(401, 'Invalid webhook secret token.');
        }

        return $next($request);
    }
}

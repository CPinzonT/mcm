<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SyncRequestUrlFromAppUrl
{
    public function handle(Request $request, Closure $next)
    {
        $appUrl = parse_url((string) config('app.url'));

        if (! empty($appUrl['host']) && ! empty($appUrl['port'])) {
            $host = $appUrl['host'].':'.$appUrl['port'];

            $request->headers->set('HOST', $host);
            $request->server->set('HTTP_HOST', $host);
            $request->server->set('SERVER_PORT', (string) $appUrl['port']);
        }

        return $next($request);
    }
}

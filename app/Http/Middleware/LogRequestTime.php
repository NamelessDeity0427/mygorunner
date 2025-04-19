<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestTime
{
    /**
     * Handle an incoming request and log its processing time.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $duration = microtime(true) - $startTime;

        Log::info('Request processed', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'duration_ms' => round($duration * 1000, 2),
            'user_id' => Auth::id() ?? 'guest',
            'ip' => $request->ip(),
        ]);

        return $response;
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GzipResponse
{
    /**
     * Compress HTTP response payloads using gzip when client supports it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! function_exists('gzencode') || ! $request->acceptsEncoding('gzip')) {
            return $response;
        }

        // Skip if already encoded or streamed
        if ($response->headers->has('Content-Encoding') || $response->headers->has('Transfer-Encoding')) {
            return $response;
        }

        $content = $response->getContent();
        // Only compress if body is larger than 1KB
        if ($content === false || strlen($content) < 1024) {
            return $response;
        }

        $compressed = gzencode($content, 6);
        if ($compressed !== false) {
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Content-Length', (string) strlen($compressed));
            $response->headers->set('Vary', 'Accept-Encoding');
        }

        return $response;
    }
}

<?php

// app/Http/Middleware/SetCorsHeaders.php

namespace App\Http\Middleware;

use Closure;

class SetCorsHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->header('Cross-Origin-Opener-Policy', 'same-origin');
        $response->header('Cross-Origin-Embedder-Policy', 'require-corp');

        return $response;
    }
}

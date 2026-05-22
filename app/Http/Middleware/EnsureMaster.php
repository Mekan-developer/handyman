<?php

namespace App\Http\Middleware;

use App\Models\Master;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMaster
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() instanceof Master) {
            return response()->json(['message' => 'Forbidden — master token required.'], 403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SystemStatusController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'queue' => $this->checkQueue(),
            'websocket' => $this->checkWebSocket(),
        ]);
    }

    private function checkQueue(): string
    {
        $heartbeat = Cache::get('queue:worker_heartbeat');

        if (! $heartbeat || now()->timestamp - $heartbeat > 120) {
            return 'error';
        }

        return 'ok';
    }

    private function checkWebSocket(): string
    {
        try {
            $scheme = config('broadcasting.connections.reverb.options.scheme', 'http');
            $host = config('broadcasting.connections.reverb.options.host', '127.0.0.1');
            $port = config('broadcasting.connections.reverb.options.port', 8081);

            Http::timeout(2)->get("{$scheme}://{$host}:{$port}/");

            return 'ok';
        } catch (\Exception) {
            return 'error';
        }
    }
}

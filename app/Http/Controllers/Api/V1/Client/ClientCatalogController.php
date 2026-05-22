<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use Illuminate\Http\JsonResponse;

class ClientCatalogController extends Controller
{
    public function cities(): JsonResponse
    {
        return response()->json([
            'data' => City::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function categories(): JsonResponse
    {
        return response()->json([
            'data' => Category::where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
                ->orderBy('name')
                ->get(['id', 'name', 'parent_id']),
        ]);
    }
}

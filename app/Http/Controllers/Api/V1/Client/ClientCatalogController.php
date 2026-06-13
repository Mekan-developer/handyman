<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Client\BannerResource;
use App\Models\Category;
use App\Models\City;
use App\Models\Oblast;
use App\Models\Region;
use App\Repositories\BannerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientCatalogController extends Controller
{
    public function __construct(private readonly BannerRepository $bannerRepository) {}

    public function banners(): AnonymousResourceCollection
    {
        return BannerResource::collection($this->bannerRepository->activeSorted());
    }

    public function cities(): JsonResponse
    {
        return response()->json([
            'data' => City::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'oblast_id']),
        ]);
    }

    public function oblasts(): JsonResponse
    {
        return response()->json([
            'data' => Oblast::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function regions(): JsonResponse
    {
        return response()->json([
            'data' => Region::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'oblast_id']),
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

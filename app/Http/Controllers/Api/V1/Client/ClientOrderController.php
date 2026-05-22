<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Actions\CreateClientOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Client\CreateClientOrderRequest;
use App\Http\Resources\Api\V1\Client\ClientOrderResource;
use App\Models\Client;
use App\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientOrderController extends Controller
{
    public function __construct(private readonly OrderRepository $repository) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var Client $client */
        $client = $request->user();

        $orders = $this->repository->forClient($client, $request->query('status'));

        return ClientOrderResource::collection($orders);
    }

    public function show(Request $request, int $id): ClientOrderResource
    {
        /** @var Client $client */
        $client = $request->user();

        $order = $this->repository->findForClientOrFail($id, $client);

        return new ClientOrderResource($order);
    }

    public function store(CreateClientOrderRequest $request, CreateClientOrderAction $action): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $data = $request->validated();
        $photos = $request->file('photos', []);
        unset($data['photos']);

        $order = $action->handle($client, $data, $photos);

        return (new ClientOrderResource($order->load(['city', 'category', 'photos'])))
            ->response()
            ->setStatusCode(201);
    }
}

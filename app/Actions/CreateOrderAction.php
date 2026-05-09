<?php

namespace App\Actions;

use App\Models\Order;
use App\OrderStatus;
use App\Repositories\OrderRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function __construct(private readonly OrderRepository $repository) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $photos
     */
    public function handle(array $data, array $photos = []): Order
    {
        return DB::transaction(function () use ($data, $photos) {
            $order = $this->repository->create([
                ...$data,
                'status' => OrderStatus::Pending,
            ]);

            foreach ($photos as $photo) {
                $path = $photo->store("orders/{$order->id}/problem", 'public');

                $order->photos()->create([
                    'path' => $path,
                    'original_name' => $photo->getClientOriginalName(),
                    'status' => 'done',
                ]);
            }

            return $order->load(['city', 'category', 'photos']);
        });
    }
}

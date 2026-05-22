<?php

namespace App\Repositories;

use App\Models\Master;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MasterRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Master::with(['city', 'categories'])
            ->latest()
            ->paginate($perPage);
    }

    /** All active masters with latest location — for map view. */
    public function forMap(?int $cityId = null): Collection
    {
        return Master::with(['city', 'latestLocation'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('access_expires_at')
                    ->orWhere('access_expires_at', '>', now());
            })
            ->when($cityId, fn ($q, $id) => $q->where('city_id', $id))
            ->get();
    }

    /** Location history for a single master — for trajectory. */
    public function trajectory(Master $master, int $hours = 8): Collection
    {
        return $master->locations()
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude', 'recorded_at']);
    }

    public function findOrFail(int $id): Master
    {
        return Master::findOrFail($id);
    }

    public function create(array $data): Master
    {
        $categories = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $master = Master::create($data);
        $master->categories()->sync($categories);

        return $master;
    }

    public function update(Master $master, array $data): Master
    {
        $categories = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $master->update($data);
        $master->categories()->sync($categories);

        return $master;
    }

    public function delete(Master $master): void
    {
        $master->delete();
    }

    public function incrementBalance(Master $master, float $amount): void
    {
        $master->increment('balance', $amount);
    }

    public function resetBalance(Master $master): void
    {
        $master->update(['balance' => 0]);
    }

    /** Active masters in given city, optionally filtered by category — for order assignment dropdown. */
    public function eligibleForOrder(int $cityId, ?int $categoryId = null): Collection
    {
        return Master::with(['categories', 'latestLocation'])
            ->where('city_id', $cityId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('access_expires_at')
                    ->orWhere('access_expires_at', '>', now());
            })
            ->when($categoryId, fn ($q, $catId) => $q->whereHas(
                'categories',
                fn ($c) => $c->where('categories.id', $catId)
            ))
            ->get();
    }
}

<?php

namespace App\Repositories;

use App\Models\City;
use Illuminate\Pagination\LengthAwarePaginator;

class CityRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return City::orderBy('name')->paginate($perPage);
    }

    public function findOrFail(int $id): City
    {
        return City::findOrFail($id);
    }

    public function create(array $data): City
    {
        return City::create($data);
    }

    public function update(City $city, array $data): City
    {
        $city->update($data);

        return $city;
    }

    public function delete(City $city): void
    {
        $city->delete();
    }
}

<?php

namespace App\Repositories;

use App\Models\Client;

class ClientRepository
{
    public function findOrFail(int $id): Client
    {
        return Client::findOrFail($id);
    }

    public function findByPhone(string $phone): ?Client
    {
        return Client::where('phone', $phone)->first();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Client
    {
        return Client::create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        return $client->fresh();
    }
}

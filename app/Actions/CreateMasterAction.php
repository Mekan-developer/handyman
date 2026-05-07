<?php

namespace App\Actions;

use App\Models\Master;
use App\Repositories\MasterRepository;

class CreateMasterAction
{
    public function __construct(private readonly MasterRepository $repository) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): Master
    {
        return $this->repository->create($data);
    }
}

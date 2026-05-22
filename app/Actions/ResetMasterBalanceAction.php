<?php

namespace App\Actions;

use App\Models\Master;
use App\Repositories\MasterRepository;

class ResetMasterBalanceAction
{
    public function __construct(private readonly MasterRepository $repository) {}

    public function handle(Master $master): void
    {
        $this->repository->resetBalance($master);
    }
}

<?php

namespace App\Actions;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class CreateCategoryAction
{
    public function __construct(private readonly CategoryRepository $repository) {}

    /**
     * @param  array{name: string, is_active: bool, parent_id: int|null}  $data
     */
    public function handle(array $data): Category
    {
        return $this->repository->create($data);
    }
}

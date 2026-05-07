<?php

namespace App\Actions;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class UpdateCategoryAction
{
    public function __construct(private readonly CategoryRepository $repository) {}

    /**
     * @param  array{name: string, is_active: bool, parent_id: int|null}  $data
     */
    public function handle(Category $category, array $data): Category
    {
        return $this->repository->update($category, $data);
    }
}

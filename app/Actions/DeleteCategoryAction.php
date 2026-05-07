<?php

namespace App\Actions;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class DeleteCategoryAction
{
    public function __construct(private readonly CategoryRepository $repository) {}

    public function handle(Category $category): void
    {
        $this->repository->delete($category);
    }
}

<?php

namespace App\Actions;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class DeleteCategoryAction
{
    public function __construct(private readonly CategoryRepository $repository) {}

    public function handle(Category $category): void
    {
        if ($category->children()->exists()) {
            throw new \RuntimeException('categories.delete_has_children');
        }

        $this->repository->delete($category);
    }
}

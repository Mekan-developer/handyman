<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Category::with('parent')
            ->orderByRaw('COALESCE(parent_id, id)')
            ->orderBy('parent_id')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /** Root categories only — used as parent options in the form. */
    public function roots(): Collection
    {
        return Category::whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function findOrFail(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}

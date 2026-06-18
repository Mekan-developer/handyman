<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Category::with(['parent', 'content.images'])
            ->orderByRaw('COALESCE(parent_id, id) DESC')
            ->orderByRaw('parent_id IS NOT NULL')
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

    /**
     * Active categories whose name matches the search term — used by the client
     * app's service search input. Matches are ordered by name with the parent
     * eager-loaded so the app can show the service's group.
     */
    public function searchActive(string $term, int $limit = 20): Collection
    {
        $escaped = addcslashes($term, '%_\\');

        return Category::where('is_active', true)
            ->where('name', 'like', "%{$escaped}%")
            ->with(['parent:id,name'])
            ->orderBy('name')
            ->limit($limit)
            ->get();
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

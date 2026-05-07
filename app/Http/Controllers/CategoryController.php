<?php

namespace App\Http\Controllers;

use App\Actions\CreateCategoryAction;
use App\Actions\DeleteCategoryAction;
use App\Actions\UpdateCategoryAction;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Traits\WithNotification;
use App\Repositories\CategoryRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use WithNotification;

    public function __construct(private readonly CategoryRepository $repository) {}

    public function index(): Response
    {
        return Inertia::render('Categories/Index', [
            'categories' => $this->repository->paginate(),
            'parentCategories' => $this->repository->roots(),
        ]);
    }

    public function store(StoreCategoryRequest $request, CreateCategoryAction $action): RedirectResponse
    {
        $action->handle($request->validated());
        $this->notifySuccess('notifications.created', ['resource' => __('resources.category')]);

        return redirect()->route('categories.index');
    }

    public function update(UpdateCategoryRequest $request, int $id, UpdateCategoryAction $action): RedirectResponse
    {
        $category = $this->repository->findOrFail($id);
        $action->handle($category, $request->validated());
        $this->notifySuccess('notifications.updated', ['resource' => __('resources.category')]);

        return redirect()->route('categories.index');
    }

    public function destroy(int $id, DeleteCategoryAction $action): RedirectResponse
    {
        $category = $this->repository->findOrFail($id);
        $action->handle($category);
        $this->notifySuccess('notifications.deleted', ['resource' => __('resources.category')]);

        return redirect()->route('categories.index');
    }
}

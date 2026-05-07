<?php

namespace App\Http\Controllers;

use App\Actions\CreateCityAction;
use App\Actions\DeleteCityAction;
use App\Actions\UpdateCityAction;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Traits\WithNotification;
use App\Repositories\CityRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CityController extends Controller
{
    use WithNotification;

    public function __construct(private readonly CityRepository $repository) {}

    public function index(): Response
    {
        return Inertia::render('Cities/Index', [
            'cities' => $this->repository->paginate(),
        ]);
    }

    public function store(StoreCityRequest $request, CreateCityAction $action): RedirectResponse
    {
        $action->handle($request->validated());
        $this->notifySuccess('notifications.created', ['resource' => __('resources.city')]);

        return redirect()->route('cities.index');
    }

    public function update(UpdateCityRequest $request, int $id, UpdateCityAction $action): RedirectResponse
    {
        $city = $this->repository->findOrFail($id);
        $action->handle($city, $request->validated());
        $this->notifySuccess('notifications.updated', ['resource' => __('resources.city')]);

        return redirect()->route('cities.index');
    }

    public function destroy(int $id, DeleteCityAction $action): RedirectResponse
    {
        $city = $this->repository->findOrFail($id);
        $action->handle($city);
        $this->notifySuccess('notifications.deleted', ['resource' => __('resources.city')]);

        return redirect()->route('cities.index');
    }
}

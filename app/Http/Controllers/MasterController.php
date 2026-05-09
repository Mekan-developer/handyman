<?php

namespace App\Http\Controllers;

use App\Actions\CreateMasterAction;
use App\Actions\DeleteMasterAction;
use App\Actions\UpdateMasterAction;
use App\Http\Requests\StoreMasterRequest;
use App\Http\Requests\UpdateMasterRequest;
use App\Http\Resources\MasterResource;
use App\Http\Traits\WithNotification;
use App\PaymentModel;
use App\Repositories\CategoryRepository;
use App\Repositories\CityRepository;
use App\Repositories\MasterRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MasterController extends Controller
{
    use WithNotification;

    public function __construct(private readonly MasterRepository $repository) {}

    public function index(): Response
    {
        return Inertia::render('Masters/Index', [
            'masters' => MasterResource::collection($this->repository->paginate()),
            'cities' => app(CityRepository::class)->paginate(100)->items(),
            'categories' => app(CategoryRepository::class)->paginate(100)->items(),
            'paymentModels' => collect(PaymentModel::cases())->map(fn ($m) => [
                'value' => $m->value,
                'label' => $m->label(),
            ]),
        ]);
    }

    public function map(): Response
    {
        $masters = $this->repository->forMap();

        return Inertia::render('Masters/Map', [
            'masters' => MasterResource::collection($masters)->resolve(),
            'cityIds' => $masters->pluck('city_id')->unique()->values(),
        ]);
    }

    public function trajectory(int $id): JsonResponse
    {
        $master = $this->repository->findOrFail($id);

        return response()->json([
            'master' => ['id' => $master->id, 'name' => $master->name],
            'points' => $this->repository->trajectory($master),
        ]);
    }

    public function store(StoreMasterRequest $request, CreateMasterAction $action): RedirectResponse
    {
        $action->handle($request->validated());
        $this->notifySuccess('notifications.created', ['resource' => __('resources.master')]);

        return redirect()->route('masters.index');
    }

    public function update(UpdateMasterRequest $request, int $id, UpdateMasterAction $action): RedirectResponse
    {
        $master = $this->repository->findOrFail($id);
        $action->handle($master, $request->validated());
        $this->notifySuccess('notifications.updated', ['resource' => __('resources.master')]);

        return redirect()->route('masters.index');
    }

    public function destroy(int $id, DeleteMasterAction $action): RedirectResponse
    {
        $master = $this->repository->findOrFail($id);
        $action->handle($master);
        $this->notifySuccess('notifications.deleted', ['resource' => __('resources.master')]);

        return redirect()->route('masters.index');
    }
}

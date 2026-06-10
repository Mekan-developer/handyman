<?php

namespace App\Http\Controllers;

use App\Actions\AssignMasterAction;
use App\Actions\CreateOrderAction;
use App\Actions\DeleteOrderAction;
use App\Actions\SetOrderFinalPriceAction;
use App\Actions\UpdateOrderAction;
use App\Actions\UpdateOrderStatusAction;
use App\Exceptions\OrderException;
use App\Http\Requests\AssignMasterToOrderRequest;
use App\Http\Requests\SetOrderFinalPriceRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Http\Traits\WithNotification;
use App\OrderStatus;
use App\Repositories\CategoryRepository;
use App\Repositories\CityRepository;
use App\Repositories\MasterRepository;
use App\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    use WithNotification;

    public function __construct(
        private readonly OrderRepository $repository,
        private readonly MasterRepository $masterRepository,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'city_id']);

        return Inertia::render('Orders/Index', [
            'orders' => OrderResource::collection($this->repository->paginate($filters)),
            'cities' => app(CityRepository::class)->paginate(100)->items(),
            'statuses' => collect(OrderStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
            ]),
            'filters' => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $order = $this->repository->findOrFail($id);

        $isPending = $order->status === OrderStatus::Pending;

        return Inertia::render('Orders/Show', [
            'order' => (new OrderResource($order))->resolve(),
            'cities' => $isPending ? app(CityRepository::class)->all()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]) : [],
            'categories' => $isPending ? app(CategoryRepository::class)->roots()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]) : [],
            'eligibleMasters' => $this->masterRepository
                ->eligibleForOrder($order->city_id, $order->category_id)
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'phone' => $m->phone,
                    'categories' => $m->categories->pluck('name'),
                    'latest_location' => $m->latestLocation ? [
                        'latitude' => $m->latestLocation->latitude,
                        'longitude' => $m->latestLocation->longitude,
                    ] : null,
                ]),
            'statuses' => collect(OrderStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
            ]),
        ]);
    }

    public function store(StoreOrderRequest $request, CreateOrderAction $action): RedirectResponse
    {
        $data = $request->validated();
        $photos = $request->file('photos', []);
        unset($data['photos']);

        $action->handle($data, $photos);
        $this->notifySuccess('notifications.created', ['resource' => __('resources.order')]);

        return redirect()->route('orders.index');
    }

    public function update(UpdateOrderRequest $request, int $id, UpdateOrderAction $action): RedirectResponse
    {
        $order = $this->repository->findOrFail($id);

        try {
            $action->handle($order, $request->validated());
            $this->notifySuccess('notifications.updated', ['resource' => __('resources.order')]);
        } catch (OrderException $e) {
            $this->notifyError($this->mapExceptionMessage($e));
        }

        return redirect()->route('orders.show', $id);
    }

    public function destroy(int $id, DeleteOrderAction $action): RedirectResponse
    {
        $order = $this->repository->findOrFail($id);
        $action->handle($order);
        $this->notifySuccess('notifications.deleted', ['resource' => __('resources.order')]);

        return redirect()->route('orders.index');
    }

    public function assign(AssignMasterToOrderRequest $request, int $id, AssignMasterAction $action): RedirectResponse
    {
        $order = $this->repository->findOrFail($id);

        try {
            $action->handle($order, (int) $request->validated()['master_id']);
            $this->notifySuccess('orders.notifications.master_assigned');
        } catch (OrderException $e) {
            $this->notifyError($this->mapExceptionMessage($e));
        }

        return redirect()->route('orders.show', $order->id);
    }

    public function setPrice(SetOrderFinalPriceRequest $request, int $id, SetOrderFinalPriceAction $action): RedirectResponse
    {
        $order = $this->repository->findOrFail($id);

        try {
            $action->handle($order, (float) $request->validated()['final_price']);
            $this->notifySuccess('orders.notifications.price_set');
        } catch (OrderException $e) {
            $this->notifyError($this->mapExceptionMessage($e));
        }

        return redirect()->route('orders.show', $order->id);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id, UpdateOrderStatusAction $action): RedirectResponse
    {
        $order = $this->repository->findOrFail($id);
        $data = $request->validated();
        $newStatus = OrderStatus::from($data['status']);

        try {
            $action->handle($order, $newStatus, $data['cancel_reason'] ?? null);
            $this->notifySuccess('orders.notifications.status_updated');
        } catch (OrderException $e) {
            $this->notifyError($this->mapExceptionMessage($e));
        }

        return redirect()->route('orders.show', $order->id);
    }

    public function masterTrajectoryForOrder(int $id): JsonResponse
    {
        $order = $this->repository->findOrFail($id);

        $points = $order->masterLocations()
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude', 'recorded_at']);

        return response()->json(['points' => $points]);
    }

    private function mapExceptionMessage(OrderException $e): string
    {
        return match ($e->getMessage()) {
            'Master access expired or master is inactive.' => __('orders.errors.master_inactive'),
            'Master city does not match the order city.' => __('orders.errors.city_mismatch'),
            'Master is not registered in the order category.' => __('orders.errors.category_mismatch'),
            'Order is already in a final status.' => __('orders.errors.already_final'),
            'Order can only be edited when status is Pending.' => __('orders.errors.not_editable'),
            default => __('orders.errors.invalid_transition'),
        };
    }
}

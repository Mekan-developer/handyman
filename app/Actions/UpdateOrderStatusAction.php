<?php

namespace App\Actions;

use App\Exceptions\OrderException;
use App\Models\Order;
use App\OrderStatus;
use App\Repositories\OrderRepository;

class UpdateOrderStatusAction
{
    public function __construct(
        private readonly OrderRepository $repository,
        private readonly CreditMasterBalanceAction $creditBalance,
    ) {}

    public function handle(Order $order, OrderStatus $newStatus, ?string $cancelReason = null): Order
    {
        if ($order->status->isFinal()) {
            throw OrderException::alreadyFinal();
        }

        if (! $this->isValidTransition($order->status, $newStatus)) {
            throw OrderException::invalidTransition($order->status->value, $newStatus->value);
        }

        $updated = $this->repository->changeStatus($order, $newStatus);

        if ($newStatus === OrderStatus::Cancelled && $cancelReason !== null) {
            $updated->update(['cancel_reason' => $cancelReason]);
        }

        if ($newStatus === OrderStatus::Completed) {
            $this->creditBalance->handle($updated->load('master'));
        }

        return $updated->fresh();
    }

    private function isValidTransition(OrderStatus $from, OrderStatus $to): bool
    {
        return match ($from) {
            OrderStatus::Pending => in_array($to, [OrderStatus::Assigned, OrderStatus::Cancelled], true),
            OrderStatus::Assigned => in_array($to, [OrderStatus::InProgress, OrderStatus::Cancelled], true),
            OrderStatus::InProgress => in_array($to, [OrderStatus::Completed, OrderStatus::Cancelled], true),
            default => false,
        };
    }
}

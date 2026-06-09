<?php

namespace App\Repositories;

use App\Models\City;
use App\Models\Master;
use App\Models\Order;
use App\OrderStatus;

class DashboardRepository
{
    /** @return array<string, int> */
    public function stats(): array
    {
        return [
            'total_orders' => Order::count(),
            'active_masters' => Master::where('is_active', true)->count(),
            'pending_orders' => Order::where('status', OrderStatus::Pending)->count(),
            'total_cities' => City::where('is_active', true)->count(),
        ];
    }
}

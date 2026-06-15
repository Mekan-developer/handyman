<?php

namespace App\Enums;

enum UserRole: string
{
    case Administrator = 'administrator';
    case Manager = 'manager';
    case Operator = 'operator';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => __('users.roles.administrator'),
            self::Manager => __('users.roles.manager'),
            self::Operator => __('users.roles.operator'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Administrator => 'indigo',
            self::Manager => 'blue',
            self::Operator => 'green',
        };
    }

    public function canManage(self $role): bool
    {
        return match ($this) {
            self::Administrator => true,
            self::Manager => $role !== self::Administrator,
            self::Operator => false,
        };
    }
}

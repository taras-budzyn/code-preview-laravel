<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Collection;

class RolesAndPermissionsService
{
    public const ADMIN_ROLE = 'admin';
    public const ACCOUNTANT_ROLE = 'accountant';
    public const CLIENT_SERVICE_ROLE = 'customer service';
    public const TRAINER_ROLE = 'trainer';
    public const CLIENT_ROLE = 'client';
    public const MANAGER_ROLE = 'manager';
    public const SUPER_ADMIN_ROLE = 'super admin';

    public function getRolesForClients(): Collection
    {
        $roles = Role::where('name', '!=', RolesAndPermissionsService::CLIENT_ROLE);

        if (!backpack_user()->hasRole(RolesAndPermissionsService::SUPER_ADMIN_ROLE)) {
            $roles->where('name', '!=', RolesAndPermissionsService::SUPER_ADMIN_ROLE);
        }

        return $roles->get();
    }
}

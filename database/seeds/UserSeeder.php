<?php

declare(strict_types = 1);

use App\Services\RolesAndPermissionsService;
use App\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        factory(User::class, 3)->create()->each(static function (User $user): void {
            $user->assignRole(RolesAndPermissionsService::TRAINER_ROLE);
        });
        factory(User::class, 5)->create([
            'status' => 'active',
        ])->each(static function (User $user): void {
            $user->assignRole(RolesAndPermissionsService::CLIENT_ROLE);
        });
        factory(User::class, 3)->create()->each(static function (User $user): void {
            $user->assignRole(RolesAndPermissionsService::CLIENT_SERVICE_ROLE);
        });
        factory(User::class)
            ->create(['email' => 'client@client.com', 'password' => bcrypt('client')])
            ->assignRole(RolesAndPermissionsService::CLIENT_ROLE);
        factory(User::class)
            ->create(['email' => 'trainer@trainer.com', 'password' => bcrypt('trainer')])
            ->assignRole(RolesAndPermissionsService::TRAINER_ROLE);
        factory(User::class)
            ->create(['email' => 'manager@manager.com', 'password' => bcrypt('manager')])
            ->assignRole(RolesAndPermissionsService::ACCOUNTANT_ROLE);
        factory(User::class)
            ->create(['email' => 'admin@admin.com', 'password' => bcrypt('secret'), 'status' => 'active'])
            ->assignRole(RolesAndPermissionsService::SUPER_ADMIN_ROLE);
    }
}

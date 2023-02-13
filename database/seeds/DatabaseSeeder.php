<?php

declare(strict_types = 1);

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
         $this->call(RolesAndPermissionsSeeder::class);
         $this->call(UserSeeder::class);
         $this->call(MembersSeeder::class);
         $this->call(BeltsSeeder::class);
         $this->call(DiscountSeeder::class);
         $this->call(CancellationSettingSeeder::class);
         $this->call(GroupSettingSeeder::class);
         $this->call(PlaceSeeder::class);
         $this->call(GroupSeeder::class);
         $this->call(MemberSubscriptionSeeder::class);
         $this->call(ScheduleMakerSeeder::class);
         $this->call(WorkoutSeeder::class);
         $this->call(EmailSeeder::class);
         $this->call(LanguagesSeeder::class);
         $this->call(ContractTemplateSeeder::class);
         $this->call(ContractSeeder::class);
         $this->call(InvoiceSeeder::class);
         $this->call(InvoiceItemSeeder::class);
         $this->call(PaymentSeeder::class);
    }
}

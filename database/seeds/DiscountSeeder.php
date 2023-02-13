<?php

declare(strict_types = 1);

use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        factory(Discount::class, 10)->create();
    }
}

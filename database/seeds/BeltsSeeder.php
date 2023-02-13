<?php

declare(strict_types = 1);

use App\Models\Belt;
use App\Models\Member;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class BeltsSeeder extends Seeder
{
    public function run(): void
    {
        $totalMember = Member::count();
        $faker = Faker::create();

        for ($i = 0; $i < 20; $i++) {
            factory(Belt::class)->create(['member_id' => $faker->numberBetween(1, $totalMember)]);
        }
    }
}

<?php

use App\Models\Contract;
use App\Models\Group;
use App\Models\Invoice;
use App\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userTotal = User::count();
        for ($i = 0; $i < 10; $i++) {
            factory(Invoice::class)->create([
                'user_id' => rand(1, $userTotal),
            ]);
        }
    }
}

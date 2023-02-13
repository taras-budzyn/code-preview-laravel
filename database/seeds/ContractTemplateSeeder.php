<?php

use App\Models\ContractTemplate;
use Illuminate\Database\Seeder;

class ContractTemplateSeeder extends Seeder
{
    public function run()
    {
        factory(ContractTemplate::class, 10)->create();
    }
}

<?php

use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Group;
use App\Models\Member;
use App\Models\MemberSubscription;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\ContractGroupHistory;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userTotal = User::count();
        $memberSubscriptionTotal = MemberSubscription::count();
        $groupTotal = Group::count();
        $contractTemplateTotal = ContractTemplate::count();
        $memberTotal = Member::count();

        for ($i = 0; $i < 10; $i++) {
            $contract = factory(Contract::class)->create([
                'representative_id' => rand(1, $userTotal),
                'user_id' => rand(1, $userTotal),
                'member_subscription_id' => rand(1, $memberSubscriptionTotal),
                'group_id' => null,
                'contract_template_id' => rand(1, $contractTemplateTotal),
                'member_id' => rand(1, $memberTotal),
            ]);

            ContractGroupHistory::create([
                'group_id' => rand(1,$groupTotal),
                'left_date' => null,
                'contract_id' => $contract->id,
                'created_at' => Carbon::now()
            ]);
        }
    }
}

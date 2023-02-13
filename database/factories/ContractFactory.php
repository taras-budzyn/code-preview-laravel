<?php

/* @var $factory Factory */

use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Group;
use App\Models\Member;
use App\Models\MemberSubscription;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Contract::class, function (Faker $faker) {
    $start = $faker->dateTimeBetween('-2 years', 'now');
    $end = $faker->dateTimeBetween($start, $start->format('Y-m-d H:i:s').' +1 year');
    $status = $faker->randomElement(Contract::STATUSES);
    return [
        'representative_id' => factory(User::class),
        'user_id' => factory(User::class),
        'member_subscription_id' => factory(MemberSubscription::class),
        'group_id' => factory(Group::class),
        'template' => $faker->word,
        'date_from' => $start,
        'date_until' => ($status === Contract::STATUS_TERMINATED) ? $end : null,
        'nvs_discount' => $faker->boolean,
        'serie' => $faker->word,
        'contract_template_id' => factory(ContractTemplate::class),
        'member_id' => factory(Member::class),
        'status' => $status,
    ];
});

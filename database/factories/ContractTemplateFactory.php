<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\ContractTemplate::class, function (Faker $faker) {
    return [
        'name' => $faker->title,
        'public_name' => $faker->word,
        'status' => $faker->randomElement(\App\Models\ContractTemplate::STATUS),
        'type' => $faker->randomElement(\App\Models\ContractTemplate::TYPE),
    ];
});

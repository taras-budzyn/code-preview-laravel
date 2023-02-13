<?php

/* @var $factory Factory */

use App\Models\Invoice;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(App\Models\Invoice::class, function (Faker $faker) {

    $createdAt = $faker->dateTimeBetween('-30 days', '-20 days');
    $total = $faker->randomFloat(2, 0, 100);

    return [
        'type' => $faker->randomElement(Invoice::getTypes()),
        'series' => Invoice::SERIES,
        'number' => $faker->randomNumber(3),
        'status' => $faker->randomElement(Invoice::getStatuses()),
        'client_name' => $faker->firstName,
        'client_surname' => $faker->lastName,
        'client_address' => $faker->address,
        'client_phone' => $faker->phoneNumber,
        'client_email' => $faker->email,
        'total' => $total,
        'amount_paid' => $faker->optional(0.5, 0.0)->randomFloat(2, 0, $total),
        'billing_from' =>  (clone $createdAt)->add(date_interval_create_from_date_string('-30 days')),
        'billing_to' =>  $createdAt,
        'issued_at' => (clone $createdAt)->add(date_interval_create_from_date_string('-1 days')),
        'sent_at' => null,
        'paid_at' => $faker->optional(0.5)->dateTimeBetween((clone $createdAt)->add(date_interval_create_from_date_string($faker->numberBetween(0, 100) . ' hours'))),
        'sent_at' => $faker->optional(0.8)->dateTimeBetween((clone $createdAt)->add(date_interval_create_from_date_string($faker->numberBetween(0, 200) . ' hours'))),
        'cancelled_at' => $faker->optional(0.2)->dateTimeBetween((clone $createdAt)->add(date_interval_create_from_date_string($faker->numberBetween(0, 30) . ' hours'))),
        'created_at' => $createdAt,
        'user_id' => 1,
        'contract_id' => 1
    ];
});

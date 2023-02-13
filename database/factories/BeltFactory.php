<?php

declare(strict_types = 1);

use App\Models\Belt;
use Faker\Generator as Faker;

//phpcs:disable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration
/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Belt::class, static function (Faker $faker) {
    return [
        'name' => $faker->randomElement(Belt::LEVELS),
        'date' => $faker->date(),
        'member_id' => 1,
    ];
});

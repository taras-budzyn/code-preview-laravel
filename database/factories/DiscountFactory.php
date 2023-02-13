<?php

declare(strict_types = 1);

use App\Models\Discount;
use Faker\Generator as Faker;

//phpcs:disable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration
/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Discount::class, static function (Faker $faker) {
    return [
        'name' => $faker->word,
        'note' => $faker->paragraph,
        'type' => $faker->randomElement(Discount::TYPE),
        'discount' => $faker->numberBetween(10, 50),
        'finvalda_id' => $faker->slug(1),
    ];
});

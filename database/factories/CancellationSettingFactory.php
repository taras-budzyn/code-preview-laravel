<?php

declare(strict_types = 1);

use App\Models\CancellationSetting;
use Faker\Generator as Faker;

//phpcs:disable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration
/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(CancellationSetting::class, static function (Faker $faker): array {
    return [
        'name' => $faker->paragraph,
    ];
});

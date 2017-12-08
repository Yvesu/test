<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});


$factory->define(App\Models\Make\MakeFilterFile::class, function (Faker\Generator $faker) {
    return [
        'user_id' => $faker->numberBetween(1000440,1111111),
        'name' => $faker->sentence,
        'cover' => 'img.cdn.hivideo.com/filter/cover/admins/1000240/16120171123111117_467*467_.jpg',
        'content' => 'file.cdn.hivideo.com/filter/hiColor/admins/1000240/16120171123111121.hicolor',
        'recommend' => 1,
    ];
});
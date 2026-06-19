<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HostCelebrity;

class CelebritySeeder extends Seeder
{
    public function run(): void
    {
        HostCelebrity::insert([

            [
                'name' => 'Dwayne Johnson',
                'birthday' => '1972-05-02',
                'profession' => 'Actor',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Robert Downey Jr',
                'birthday' => '1965-04-04',
                'profession' => 'Actor',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Cristiano Ronaldo',
                'birthday' => '1985-02-05',
                'profession' => 'Football Player',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
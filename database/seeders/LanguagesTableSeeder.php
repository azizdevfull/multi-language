<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Insert languages into the 'languages' table
        DB::table('languages')->insert([
            [
                'code' => 'en',
                'name' => 'English',
                'status' => 1, // Active
            ],
            [
                'code' => 'es',
                'name' => 'Spanish',
                'status' => 1, // Active
            ],
            [
                'code' => 'fr',
                'name' => 'French',
                'status' => 1, // Active
            ],
            [
                'code' => 'de',
                'name' => 'German',
                'status' => 1, // Active
            ],
            [
                'code' => 'ru',
                'name' => 'Russian',
                'status' => 1, // Active
            ],
            [
                'code' => 'zh',
                'name' => 'Chinese',
                'status' => 1, // Active
            ],
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'status' => 1, // Active
            ],
            [
                'code' => 'uz',
                'name' => 'Uzbek',
                'status' => 1, // Active
            ],
            [
                'code' => 'kk',
                'name' => 'Kazakh',
                'status' => 1, // Active
            ]
        ]);
    }
}

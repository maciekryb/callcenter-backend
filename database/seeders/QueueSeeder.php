<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Dodajemy przykładowe dane do tabeli 'queues'
        DB::table('queues')->insert([
            ['name' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'sales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'resales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'technical_support', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

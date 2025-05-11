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
            ['name' => 'Ogólna', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sprzedaż', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Do sprzedaż', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pomoc techniczna', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

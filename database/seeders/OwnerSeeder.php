<?php

namespace Database\Seeders;

use App\Models\Owner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OwnerSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Owner::insert([
            ['name' => 'João Silva', 'email' => 'joao@empresa.com', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maria Souza', 'email' => 'maria@empresa.com', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pedro Santos', 'email' => 'pedro@empresa.com', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

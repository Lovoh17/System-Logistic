<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class seed extends Seeder
{
    public function run(): void
    {
        $this->call([
            DatabaseSeeder::class,
            AsignarSucursalesSeeder::class,
        ]);
    }
}
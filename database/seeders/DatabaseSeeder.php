<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'development', 'testing'])) {
            $this->command?->warn('DatabaseSeeder skips demo content outside local/testing. Run DemoDataSeeder manually if needed.');

            return;
        }

        $this->call(DemoDataSeeder::class);
    }
}

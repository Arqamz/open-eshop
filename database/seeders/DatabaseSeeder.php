<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Customer::factory(10)->create();
        Admin::factory(10)->create();
    }
}

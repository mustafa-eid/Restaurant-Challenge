<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::factory()->count(5)->create();

        // Example specific branch
        Branch::create([
            'name' => 'Main Branch',
            'retention_rate' => 10.50,
        ]);
    }
}

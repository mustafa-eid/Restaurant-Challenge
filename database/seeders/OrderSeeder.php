<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Branch;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();

        Order::factory()->count(15)->create()->each(function ($order) use ($branches) {
            $order->branch_id = $branches->random()->id;
            $order->save();
        });
    }
}

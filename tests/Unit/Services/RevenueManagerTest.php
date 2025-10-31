<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\RevenueManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_total_revenue()
    {
        $order1 = Order::factory()->create();

        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'quantity' => 2,
            'price' => 10.00,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'quantity' => 5,
            'price' => 10.50,
        ]);

        $order2 = Order::factory()->create();

        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'quantity' => 3,
            'price' => 15.00,
        ]);

        $expectedTotalRevenue = (2 * 10.00) + (5 * 10.50) + (3 * 15.00);
        $actualTotalRevenue = RevenueManager::calculateTotalRevenue();

        $this->assertEquals($expectedTotalRevenue, $actualTotalRevenue);
    }
}

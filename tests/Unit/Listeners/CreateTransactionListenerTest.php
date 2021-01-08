<?php

namespace Tests\Unit\Listeners;

use App\Events\Order\OrderPaid;
use App\Events\Order\OrderPaymentFailed;
use App\Listeners\Order\CreateTransaction;
use App\Listeners\Order\MarkOrderPaymentFailed;
use App\Models\Order;
use App\Models\User;
use Tests\TestCase;

class CreateTransactionListenerTest extends TestCase
{
    public function test_it_creates_a_transaction()
    {
        $event = new OrderPaid(
            // create order
            $order = Order::factory()->create([
                // attach user to order
                'user_id' => User::factory()->create()
            ])
        );

        $listener = new CreateTransaction();

        // required authentic event to extract out the order, we pass Order as dependency
        $listener->handle($event);

        $this->assertDatabaseHas('transactions', [
            'order_id' => $order->id,
            'total' => $order->total()->amount()
        ]);
    }
}

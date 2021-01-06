<?php

namespace Tests\Unit\Listeners;

use App\Cart\Cart;
use App\Events\Order\OrderPaymentFailed;
use App\Listeners\Order\EmptyCart;
use App\Listeners\Order\MarkOrderPaymentFailed;
use App\Models\Order;
use App\Models\ProductVariation;
use App\Models\User;
use Tests\TestCase;

class MarkOrderPaymentFailedListenerTest extends TestCase
{
    public function test_it_marks_order_as_payment_failed()
    {
        $event = new OrderPaymentFailed(
            $order = Order::factory()->create([
                'user_id' => User::factory()->create()
            ])
        );

//        dd($event); // to see our order is there
        $listener = new MarkOrderPaymentFailed();

        // required authentic event to extract out the order, we pass Order as dependency
        // when we handle
        $listener->handle($event);
        //

//        dd();
        // We check that the payment being mocked as fail
        $this->assertEquals($order->fresh()->status, Order::PAYMENT_FAILED);
    }
}

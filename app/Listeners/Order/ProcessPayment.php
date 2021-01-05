<?php

namespace App\Listeners\Order;

use App\Cart\Cart;
use App\Cart\Payments\Gateway;
use App\Events\Order\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessPayment implements ShouldQueue
{
    protected $gateway;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Handle the event.
     *
     * @param  OrderCreated  $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        $order = $event->order;
//        dd('a');
        // with user x
        $this->gateway->withUser($order->user)
        // get customer
        ->getCustomer()
        // charge the user using payment method that comes from Order model
        ->charge(
            $order->paymentMethod, $order->total()->amount()
        );
    }
}
<?php

namespace Tests\Unit\Listeners;

use App\Cart\Payments\Gateways\StripeGateway;
use App\Cart\Payments\Gateways\StripeGatewayCustomer;
use App\Events\Order\OrderCreated;
use App\Events\Order\OrderPaid;
use App\Events\Order\OrderPaymentFailed;
use App\Exceptions\PaymentFailedException;
use App\Listeners\Order\ProcessPayment;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;



class ProcessPaymentListenerTest extends TestCase
{
    public function test_it_charges_the_chosen_payment_the_correct_amount()
    {
        // We fire fake since if it fired real Event it would fire email, sms etc and you may not want to do that as part of this test
        Event::fake();

        list($user, $payment, $order, $event) = $this->createEvent();
        list($gateway, $customer) = $this->mockFlow();

        // testing that charge method is called.
        $customer->shouldReceive('charge')->with(
            $order->paymentMethod, $order->total()->amount()
        );

        $listener = new ProcessPayment($gateway);

        $listener->handle($event);
    }

    public function test_it_fires_the_order_paid_event()
    {
        Event::fake();

        list($user, $payment, $order, $event) = $this->createEvent();
        list($gateway, $customer) = $this->mockFlow();

        $customer->shouldReceive('charge')->with(
            $order->paymentMethod, $order->total()->amount()
        );

        $listener = new ProcessPayment($gateway);

        $listener->handle($event);

        // check that order paid is successfully dispatched.
        // We going to use the order and compare the event data to this.
        Event::assertDispatched(OrderPaid::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }

    public function test_it_fires_the_order_failed_event()
    {
        Event::fake();

        list($user, $payment, $order, $event) = $this->createEvent();
        list($gateway, $customer) = $this->mockFlow();

        $customer->shouldReceive('charge')->with(
            $order->paymentMethod, $order->total()->amount()
        )
            // Throw an error
            ->andThrow(PaymentFailedException::class);

        $listener = new ProcessPayment($gateway);

        $listener->handle($event);

        // Now OrderPaymentFailed should be throw with this data
        Event::assertDispatched(OrderPaymentFailed::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }

    protected function createEvent()
    {
        $user = User::factory()->create();

        $payment = PaymentMethod::factory()->create([
            'user_id' => $user->id
        ]);

        $event = new OrderCreated(
            $order = Order::factory()->create([
                // assign user
                'user_id' => $user->id,
                'payment_method_id' => $payment->id
            ])
        );

        return [$user, $payment, $order, $event];
    }

    protected function mockFlow()
    {
        //        dd($event);
        // Now we Mock ProccessPayment.php $this->gateway
        $gateway = Mockery::mock(StripeGateway::class);

//        dd($gateway);
        $gateway->shouldReceive('withUser')
            // When want it to return gateway back when we call that method
            ->andReturn($gateway)
            // it should also be calling getCustomer
            ->shouldReceive('getCustomer')
            // returns customer gateway instance
            ->andReturn(
                $customer = Mockery::mock(StripeGatewayCustomer::class)
            );

        return [$gateway, $customer];
    }
}

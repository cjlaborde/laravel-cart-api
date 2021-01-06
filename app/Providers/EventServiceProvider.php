<?php

namespace App\Providers;

use App\Events\Order\OrderPaid;
use App\Events\Orders\OrderPaymentFailed;
use App\Listeners\Order\MarkOrderPaymentFailed;
use App\Listeners\Order\MarkOrderProcessing;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\Order\OrderCreated;
use App\Listeners\Order\EmptyCart;
use App\Listeners\Order\ProcessPayment;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderCreated::class => [
            ProcessPayment::class,
            EmptyCart::class
        ],
        OrderPaymentFailed::class => [
            MarkOrderPaymentFailed::class
            // you could create another listener to send email as well when order fails
        ],
        OrderPaid::class => [
            MarkOrderProcessing::class
            // you could create another listener to send email as well when order fails
        ],
        Registered::class => [
            SendEmailVerificationNotification::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

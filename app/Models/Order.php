<?php

namespace App\Models;

use App\Cart\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // we can also show these in a status if we want to.
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const PAYMENT_FAILED = 'payment_failed';
    const COMPLETED = 'completed';

    protected $fillable = [
        'status',
        'address_id',
        'shipping_method_id',
        'payment_method_id',
        'subtotal'
    ];

    public static function boot()
    {
        // when ever you create a order it will set status to pending
        parent::boot();

        static::creating(function ($order) {
            $order->status = self::PENDING;
        });
    }

    public function getSubtotalAttribute($subtotal)
    {
        return new Money($subtotal);
    }

    public function total()
    {
        // add is a method from Money.php
        return $this->subtotal->add($this->shippingMethod->price);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function products()
    {
        return $this->belongsToMany(ProductVariation::class, 'product_variation_order')
            ->withPivot(['quantity'])
            ->withTimestamps();
    }
}

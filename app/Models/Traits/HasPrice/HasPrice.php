<?php

namespace App\Models\Traits\HasPrice;

use App\Cart\Money;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use NumberFormatter;

trait HasPrice
{
    public function getPriceAttribute($value)
    {
        // original value we have in database
        // When we try to Access Price attribute
        // It will automatically give us Custom Money class
        return new Money($value);
    }

//    public function formattedPrice()
    public function getFormattedPriceAttribute()
    {
        return $this->price->formatted();
    }
}

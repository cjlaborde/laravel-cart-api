<?php

namespace App\Cart;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
// use alias since we don't want conflicting names to be a problem
use Money\Formatter\IntlMoneyFormatter;
use Money\Money as BaseMoney;
use NumberFormatter;

class Money
{
    protected $money;

    public function __construct($value)
    {
       $this->money = new BaseMoney($value, new Currency('USD'));
    }

    public function amount()
    {
        // returns the price in integer form instead of Money collection
        return $this->money->getAmount();
    }

    public function formatted()
    {
        $formatter = new IntlMoneyFormatter(
            new NumberFormatter('en_US', NumberFormatter::CURRENCY,),
            new ISOCurrencies()
        );

        return $formatter->format($this->money);
    }

    public function add(Money $money)
    {
        // what we doing is passing the money we want to add on, then reassign it with the underlying version
        // instance of the price we want to add on. Money represent our class not the underlying instance
        $this->money = $this->money->add($money->instance());

        // return $this so we can continue chaining
        return $this;
    }

    public function instance()
    {
        return $this->money;
    }
}

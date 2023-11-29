<?php

abstract class CreditPaymentButton
{
    protected bool $enabled = false;
    protected SellableItem $sellable;

    public function __construct(SellableItem $item)
    {
        $this->enabled = false;
        $this->sellable = $item;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    public function checkStockPrice()
    {
        if ($this->sellable->getStockAmount()<1) {
            throw new Exception("Not enough stock amount");
        }

    }
    abstract public function renderButton();

}

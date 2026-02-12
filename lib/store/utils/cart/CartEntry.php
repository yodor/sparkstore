<?php
include_once("store/utils/SellableItem.php");

class CartEntry
{
    protected float $line_total = 0.0;
    protected int $qty = 0;
    protected SellableItem $item;

    public function __construct(SellableItem $item, int $qty=1)
    {
        $this->item = $item;
        $this->qty = $qty;
        $this->calculate();
    }

    public function getItem(): SellableItem
    {
        return $this->item;
    }

    public function getQuantity(): int
    {
        return $this->qty;
    }

    public function setQuantity(int $qty) : void
    {
        $this->qty = $qty;
        $this->calculate();
    }

    public function increment(int $number=1) : void
    {
        $this->qty = $this->qty + $number;
        $this->calculate();
    }

    public function decrement(int $number=1) : void
    {
        if ( ($this->qty - $number) > -1 ) {
            $this->qty-=$number;
            $this->calculate();
        }
    }

    public function getPrice(): float
    {
        return $this->item->getPriceInfo()->getSellPrice();
    }

    public function getLineTotal(): float
    {
        return $this->line_total;
    }

    /**
     * Calculate line total
     */
    protected function calculate() : void
    {
        $this->line_total = $this->qty * $this->getPrice();
    }

}
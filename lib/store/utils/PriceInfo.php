<?php


class PriceInfo implements JsonSerializable {

    protected $sell_price = 0.0;
    protected $old_price = 0.0;

    protected $discount_percent = 0;

    public function __construct(float $sell_price, float $old_price, int $discount_percent)
    {
        $this->sell_price = $sell_price;
        $this->old_price = $old_price;
        $this->discount_percent = $discount_percent;
    }

    public function getSellPrice() : float
    {
        return $this->sell_price;
    }

    public function getOldPrice() : float
    {
        return $this->old_price;
    }


    public function getDiscountPercent() : int
    {
        return $this->discount_percent;
    }

    public function jsonSerialize() : array
    {
        return get_object_vars($this);
    }
}
?>
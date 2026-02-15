<?php

class DeliveryOption {

    const int NONE = 0;
    const int USER_ADDRESS = 1;
    const int COURIER_OFFICE = 2;

    protected int $id = -1;
    protected string $title = "";
    protected float $price = 0.0;

    public function __construct(int $id, string $title, float $price)
    {
        $this->id = $id;
        $this->title = $title;
        $this->price = $price;
    }

    public function getID() : int {
        return $this->id;
    }

    public function getTitle() : string {
        return $this->title;
    }

    public function getPrice() : float  {
        return $this->price;
    }

    public function setPrice(float $price) : void {
        $this->price = $price;
    }

    /**
     * return all the supported delivery options
     * @return int[]
     */
    public static function Supported() : array
    {
        return array(DeliveryOption::USER_ADDRESS, DeliveryOption::COURIER_OFFICE);
    }
}
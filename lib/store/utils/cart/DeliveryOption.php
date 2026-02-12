<?php

class DeliveryOption {

    const NONE = 0;
    const USER_ADDRESS = 1;
    const COURIER_OFFICE = 2;

    protected $id = -1;
    protected $title = "";
    protected $price = 0.0;

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

    public function setPrice(float $price) {
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
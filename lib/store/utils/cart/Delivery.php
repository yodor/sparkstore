<?php
include_once("beans/ConfigBean.php");
include_once("store/utils/cart/DeliveryCourier.php");
include_once("store/courier/Couriers.php");

class Delivery
{

    protected $couriers = array();

    protected $selected_courier = NULL;

    public function __construct()
    {
        //the couriers of the store (Couriers - Econt, Speedy and the registered ones)
        foreach (Couriers::All() as $id => $courier) {
            $this->couriers[$id] = new DeliveryCourier($id, $courier->title());
        }
    }

    public function initialize()
    {

        $config = ConfigBean::Factory();
        $config->setSection("delivery_options");

        foreach ($this->couriers as $id => $courier) {
            if ($courier instanceof DeliveryCourier) {

                $config_name = $this->configPrefix($id, "enabled");
                //radiofield
                $enabled = $config->get($config_name, array(0, 0));

                if (!$enabled[0]) {
                    unset($this->couriers[$id]);
                    if ($this->selected_courier && $this->selected_courier->getID() == $id) {
                        $this->selected_courier = NULL;
                    }
                }
                else {
                    $courier->initialize();
                }

            }
        }

    }

    public function configPrefix(int $id, string $name): string
    {
        return "courier_{$id}_$name";
    }

    public function setSelectedCourier(int $id)
    {
        if (isset($this->couriers[$id])) {
            $this->selected_courier = $this->couriers[$id];
        }
        else {
            $this->selected_courier = NULL;
        }
    }

    public function getSelectedCourier(): ?DeliveryCourier
    {
        return $this->selected_courier;
    }

    public function getCouriers(): array
    {
        return $this->couriers;
    }

    public function getCourier(int $id): DeliveryCourier
    {
        if (isset($this->couriers[$id])) {
            return $this->couriers[$id];
        }
        throw new Exception("Delivery courier ID:$id is not initialized");
    }

}
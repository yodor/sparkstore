<?php
include_once("beans/ConfigBean.php");
include_once("store/utils/cart/DeliveryOption.php");

class DeliveryCourier {

    const NONE = 0;
    const COURIER_EKONT = 1;
    const COURIER_SPEEDY = 2;

    protected $id = -1;
    protected $title = "";

    protected $options = array();

    protected $selected_option = NULL;


    public function __construct(int $id, string $title)
    {
        $this->id = $id;
        $this->title = $title;

        $option1 = new DeliveryOption(DeliveryOption::USER_ADDRESS,"Доставка до адрес", 0.0);
        $this->options[$option1->getID()] = $option1;

        $option2 = new DeliveryOption(DeliveryOption::COURIER_OFFICE, "Доставка до офис на куриер", 0.0);
        $this->options[$option2->getID()] = $option2;

    }

    public function initialize()
    {
        $config = ConfigBean::Factory();
        $config->setSection("delivery_options");

        foreach ($this->options as $optionID=>$option) {
            if ($option instanceof DeliveryOption) {
                $price = $config->get($this->configPrefix($optionID, "price"), 0.0);
                $option->setPrice($price);
                //checkbox field
                $enabled = $config->get($this->configPrefix($optionID, "enabled"), false);
                if (!$enabled) {
                    unset($this->options[$optionID]);
                }
            }
        }
    }

    public function configPrefix(int $optionID, string $name) : string
    {
        return "courier_{$this->id}_option_{$optionID}_{$name}";
    }

    public function getID() : int {
        return $this->id;
    }

    public function getTitle() : string {
        return $this->title;
    }

    /**
     * return all the supported delivery couriers
     * @return int[]
     */
    public static function Supported() : array
    {
        return array(DeliveryCourier::COURIER_EKONT, DeliveryCourier::COURIER_SPEEDY);
    }

    public function setSelectedOption(int $id)
    {
        if (isset($this->options[$id])) {
            $this->selected_option = $this->options[$id];
        }
        else {
            $this->selected_option = null;
        }
    }

    public function getSelectedOption() : ?DeliveryOption
    {
        return $this->selected_option;
    }

    public function getOptions() : array
    {
        return $this->options;
    }

    public function getOption(int $id) : DeliveryOption
    {
        if (isset($this->options[$id])) {
            return $this->options[$id];
        }
        throw new Exception("Delivery option ID:$id is not initialized");
    }
}

?>
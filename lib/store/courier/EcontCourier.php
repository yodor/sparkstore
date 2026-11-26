<?php
include_once("store/courier/ICourier.php");
include_once("store/courier/EcontOffices.php");

/**
 * Econt - its office list is public (no account)
 */
class EcontCourier implements ICourier
{
    const int ID = 1;

    public function id() : int
    {
        return EcontCourier::ID;
    }

    public function title() : string
    {
        return "Econt";
    }

    public function offices() : ?ICourierOffices
    {
        return new EcontOffices();
    }

    public function settings(InputForm $form, InputGroup $group) : void
    {
    }
}

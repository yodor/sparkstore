<?php
include_once("store/courier/ICourierOffices.php");
include_once("forms/InputForm.php");

/**
 * A courier of the store (Couriers): its ID (stored with the orders, the delivery config keys courier_<id>_...), its
 * title (an English text - a key of the language packs), its office list and its settings in the admin
 * (Orders > Delivery). A site or a plugin adds a courier with Couriers::Register().
 */
interface ICourier
{
    public function id() : int;

    public function title() : string;

    /**
     * The API of its offices - null for a courier without an office list
     */
    public function offices() : ?ICourierOffices;

    /**
     * Its own settings (an API account ...) in its group of the delivery config form - the names start with
     * Couriers::SettingName($this->id(), '') (the config section 'delivery_options')
     */
    public function settings(InputForm $form, InputGroup $group) : void;
}

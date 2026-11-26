<?php
include_once("store/courier/ICourier.php");
include_once("store/courier/EcontCourier.php");
include_once("store/courier/SpeedyCourier.php");
include_once("beans/ConfigBean.php");

/**
 * The couriers of the store - Econt and Speedy by default; a site or a plugin registers more in its init
 * (Couriers::Register(new MyCourier())). The delivery (Delivery), its config form, the office lists and the
 * checkout office step take them from here.
 */
class Couriers
{
    /**
     * @var array<int, ICourier>|null
     */
    protected static ?array $couriers = null;

    /**
     * @return array<int, ICourier> id => courier, in the order of registration
     */
    public static function All() : array
    {
        if (is_null(Couriers::$couriers)) {
            Couriers::$couriers = array();
            Couriers::Register(new EcontCourier());
            Couriers::Register(new SpeedyCourier());
        }
        return Couriers::$couriers;
    }

    /**
     * Add '$courier' (or replace the courier of its ID)
     */
    public static function Register(ICourier $courier) : void
    {
        if (is_null(Couriers::$couriers)) Couriers::All();
        Couriers::$couriers[$courier->id()] = $courier;
    }

    public static function Get(int $id) : ?ICourier
    {
        return Couriers::All()[$id] ?? null;
    }

    /**
     * The name of a setting of the courier '$id' in the config section 'delivery_options': courier_<id>_<name>
     */
    public static function SettingName(int $id, string $name) : string
    {
        return "courier_{$id}_" . $name;
    }

    /**
     * A setting of the courier '$id' (the admin Orders > Delivery)
     */
    public static function Setting(int $id, string $name) : string
    {
        $config = ConfigBean::Factory();
        $config->setSection("delivery_options");
        return trim((string)$config->get(Couriers::SettingName($id, $name), ""));
    }
}

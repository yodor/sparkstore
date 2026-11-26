<?php
include_once("store/courier/ICourier.php");
include_once("store/courier/SpeedyOffices.php");
include_once("input/processors/SecretInputProcessor.php");
include_once("store/courier/Couriers.php");

/**
 * Speedy - its office list needs an API account: the admin Orders > Delivery (user name, password); a site that
 * still has config/speedy.php (array('username' => .., 'password' => ..)) uses it until the admin has one
 */
class SpeedyCourier implements ICourier
{
    const int ID = 2;

    public function id() : int
    {
        return SpeedyCourier::ID;
    }

    public function title() : string
    {
        return "Speedy";
    }

    public function offices() : ?ICourierOffices
    {
        return new SpeedyOffices($this);
    }

    public function settings(InputForm $form, InputGroup $group) : void
    {
        $name = Couriers::SettingName(SpeedyCourier::ID, "username");
        $field = DataInputFactory::Create(InputType::TEXT, $name, tr("Speedy API user name"), 0);
        $form->addInput($field, $group);

        //the page never shows it - empty keeps the stored one
        $name = Couriers::SettingName(SpeedyCourier::ID, "password");
        $field = DataInputFactory::Create(InputType::PASSWORD, $name, tr("Speedy API password (empty - keep the saved one)"), 0);
        new SecretInputProcessor($field);
        $form->addInput($field, $group);
    }

    /**
     * array('username' => .., 'password' => ..) - the admin settings, else config/speedy.php of the site
     * @throws Exception without an account
     */
    public function account() : array
    {
        $username = Couriers::Setting(SpeedyCourier::ID, "username");
        $password = Couriers::Setting(SpeedyCourier::ID, "password");
        if ($username !== "" && $password !== "") return array("username" => $username, "password" => $password);

        $file = Spark::Get(Config::APP_PATH) . "/config/speedy.php";
        if (is_file($file)) {
            $account = include($file);
            if (is_array($account) && !empty($account["username"]) && !empty($account["password"])) {
                return array("username" => (string)$account["username"], "password" => (string)$account["password"]);
            }
        }
        throw new Exception(tr("The Speedy API account is not set (Orders > Delivery)"));
    }
}

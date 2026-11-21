<?php
include_once("store/courier/ICourierOffices.php");
include_once("store/courier/CourierAPI.php");

/**
 * Speedy offices: POST https://api.speedy.bg/v1/location/office/ {countryId: 100} - every office and locker of
 * Bulgaria in one answer (~1400, 3.5 MB). Kept: type OFFICE, cash accepted, pick up allowed, valid today.
 * The account: config/speedy.php of the site (not in git) returning array('username' => .., 'password' => ..).
 */
class SpeedyOffices implements ICourierOffices
{
    const string URL = "https://api.speedy.bg/v1/location/office/";
    const int COUNTRY_BULGARIA = 100;

    public function fetch() : array
    {
        $file = Spark::Get(Config::APP_PATH) . "/config/speedy.php";
        if (!is_file($file)) throw new Exception("Speedy account not configured (config/speedy.php)");
        $account = include($file);
        if (!is_array($account) || empty($account["username"]) || empty($account["password"])) {
            throw new Exception("config/speedy.php: username and password expected");
        }

        $json = CourierAPI::Post(SpeedyOffices::URL, array(
            "userName" => (string)$account["username"],
            "password" => (string)$account["password"],
            "language" => "BG",
            "countryId" => SpeedyOffices::COUNTRY_BULGARIA,
        ));

        $today = date("Y-m-d");
        $offices = array();
        foreach ($json["offices"] ?? array() as $office) {
            if (($office["type"] ?? "") !== "OFFICE") continue;
            if (empty($office["cashPaymentAllowed"]) || empty($office["pickUpAllowed"])) continue;
            if (($office["validFrom"] ?? "") > $today || ($office["validTo"] ?? "9999") < $today) continue;
            $address = $office["address"] ?? array();
            $offices[] = array(
                "office_key" => (string)$office["id"],
                "city" => (string)($address["siteName"] ?? ""),
                "post_code" => (string)($address["postCode"] ?? ""),
                //Speedy names are capitals: 'ХАСКОВО - СКЛАД' - shown as 'Хасково - Склад'
                "name" => mb_convert_case(mb_strtolower((string)($office["name"] ?? "")), MB_CASE_TITLE, "UTF-8"),
                "address" => (string)($address["localAddressString"] ?? ""),
            );
        }
        return $offices;
    }
}

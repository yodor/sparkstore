<?php
include_once("store/courier/ICourierOffices.php");
include_once("store/courier/CourierAPI.php");

/**
 * Speedy offices: POST https://api.speedy.bg/v1/location/office/ {countryId: 100} - every office and locker of
 * Bulgaria in one answer (~1400, 3.5 MB). Kept: type OFFICE, cash accepted, pick up allowed, valid today.
 * The account: SpeedyCourier::account() - the admin Orders > Delivery (or config/speedy.php of an older site).
 */
class SpeedyOffices implements ICourierOffices
{
    const string URL = "https://api.speedy.bg/v1/location/office/";
    const int COUNTRY_BULGARIA = 100;

    public function __construct(protected SpeedyCourier $courier)
    {
    }

    public function fetch() : array
    {
        $account = $this->courier->account();

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

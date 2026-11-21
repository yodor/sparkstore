<?php
include_once("store/courier/ICourierOffices.php");
include_once("store/courier/CourierAPI.php");

/**
 * Econt offices: POST https://ee.econt.com/services/Nomenclatures/NomenclaturesService.getOffices.json
 * {countryCode: "BGR"} - public, no account (~650, 1.9 MB). Kept: offices (isAPS - the lockers - out);
 * cash on delivery is accepted at every Econt office. The key is the office 'code' (the shipments use it).
 */
class EcontOffices implements ICourierOffices
{
    const string URL = "https://ee.econt.com/services/Nomenclatures/NomenclaturesService.getOffices.json";

    public function fetch() : array
    {
        $json = CourierAPI::Post(EcontOffices::URL, array("countryCode" => "BGR"));

        $offices = array();
        foreach ($json["offices"] ?? array() as $office) {
            if (!empty($office["isAPS"])) continue;
            if ((string)($office["code"] ?? "") === "") continue;
            $address = $office["address"] ?? array();
            $city = $address["city"] ?? array();
            $offices[] = array(
                "office_key" => (string)$office["code"],
                "city" => (string)($city["name"] ?? ""),
                "post_code" => (string)($city["postCode"] ?? ""),
                "name" => trim((string)($office["name"] ?? "")),
                "address" => EcontOffices::Address($address),
            );
        }
        return $offices;
    }

    /**
     * The address without the city (the text of the office adds the city): quarter, street and number -
     * else the full address
     */
    public static function Address(array $address) : string
    {
        $street = trim(trim((string)($address["street"] ?? "")) . " " . trim((string)($address["num"] ?? "")));
        $parts = array_filter(array(trim((string)($address["quarter"] ?? "")), $street), fn($part) => $part !== "");
        $text = implode(", ", $parts);
        if ($text === "") $text = (string)($address["fullAddress"] ?? "");
        return trim((string)preg_replace("/\\s+/u", " ", $text));
    }
}

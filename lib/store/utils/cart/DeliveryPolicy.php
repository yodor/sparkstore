<?php
include_once("beans/ConfigBean.php");
include_once("store/utils/cart/Delivery.php");

/**
 * The delivery and return terms of the store for the search engines (JSON-LD of the product offers - Google merchant
 * listings): what the terms page says, as data. Set in the admin delivery settings (Orders > Delivery, section
 * 'delivery_options'); the defaults are the EU distance-selling baseline - Bulgaria, 14 days to return, the client
 * pays the return shipping, delivery in 1-2 working days. They must match the store's terms page.
 *
 *   shippingDetails: one OfferShippingDetails per price of the enabled courier options (the prices the cart charges)
 *   hasMerchantReturnPolicy: the return window, by mail, who pays the return shipping
 */
class DeliveryPolicy
{
    const string SECTION = "delivery_options";

    //the two-letter country (ISO 3166-1) the store delivers to and takes returns from
    const string KEY_COUNTRY = "policy_country";
    //working days from the order to the delivery (handling + transit)
    const string KEY_DAYS_MIN = "policy_delivery_days_min";
    const string KEY_DAYS_MAX = "policy_delivery_days_max";
    //days to return from the receipt - 0: returns not accepted
    const string KEY_RETURN_DAYS = "policy_return_days";
    //checkbox: the store pays the return shipping
    const string KEY_FREE_RETURN = "policy_free_return";

    const string DEFAULT_COUNTRY = "BG";
    const int DEFAULT_DAYS_MIN = 1;
    const int DEFAULT_DAYS_MAX = 2;
    const int DEFAULT_RETURN_DAYS = 14;

    protected static function Config() : ConfigBean
    {
        $config = ConfigBean::Factory();
        $config->setSection(DeliveryPolicy::SECTION);
        return $config;
    }

    public static function Country() : string
    {
        $country = strtoupper(trim((string)DeliveryPolicy::Config()->get(DeliveryPolicy::KEY_COUNTRY, DeliveryPolicy::DEFAULT_COUNTRY)));
        return preg_match("/^[A-Z]{2}$/", $country) ? $country : DeliveryPolicy::DEFAULT_COUNTRY;
    }

    protected static function Days(string $key, int $default) : int
    {
        $value = trim((string)DeliveryPolicy::Config()->get($key, (string)$default));
        return ($value === "" || !is_numeric($value)) ? $default : max(0, (int)$value);
    }

    /**
     * The prices of the enabled courier options, lowest first, each once
     * @return float[]
     */
    public static function Rates() : array
    {
        $delivery = new Delivery();
        $delivery->initialize();
        $rates = array();
        foreach ($delivery->getCouriers() as $courier) {
            if (!$courier instanceof DeliveryCourier) continue;
            foreach ($courier->getOptions() as $option) {
                if (!$option instanceof DeliveryOption) continue;
                $price = round($option->getPrice(), 2);
                $rates[sprintf("%0.2f", $price)] = $price;
            }
        }
        ksort($rates, SORT_NUMERIC);
        return array_values($rates);
    }

    /**
     * The shipping of an offer in '$currency' (the courier prices): a list of OfferShippingDetails, empty when no
     * courier option is enabled
     */
    public static function ShippingDetails(string $currency) : array
    {
        $min = DeliveryPolicy::Days(DeliveryPolicy::KEY_DAYS_MIN, DeliveryPolicy::DEFAULT_DAYS_MIN);
        $max = max($min, DeliveryPolicy::Days(DeliveryPolicy::KEY_DAYS_MAX, DeliveryPolicy::DEFAULT_DAYS_MAX));
        $country = DeliveryPolicy::Country();

        $details = array();
        foreach (DeliveryPolicy::Rates() as $price) {
            $details[] = array(
                "@type" => "OfferShippingDetails",
                "shippingRate" => array(
                    "@type" => "MonetaryAmount",
                    "value" => sprintf("%0.2f", $price),
                    "currency" => $currency,
                ),
                "shippingDestination" => array("@type" => "DefinedRegion", "addressCountry" => $country),
                //the terms count the days from the order: no separate handling time
                "deliveryTime" => array(
                    "@type" => "ShippingDeliveryTime",
                    "handlingTime" => array("@type" => "QuantitativeValue", "minValue" => 0, "maxValue" => 0, "unitCode" => "DAY"),
                    "transitTime" => array("@type" => "QuantitativeValue", "minValue" => $min, "maxValue" => $max, "unitCode" => "DAY"),
                ),
            );
        }
        return $details;
    }

    /**
     * The return policy of the offers (MerchantReturnPolicy)
     */
    public static function ReturnPolicy() : array
    {
        $days = DeliveryPolicy::Days(DeliveryPolicy::KEY_RETURN_DAYS, DeliveryPolicy::DEFAULT_RETURN_DAYS);
        $policy = array(
            "@type" => "MerchantReturnPolicy",
            "applicableCountry" => DeliveryPolicy::Country(),
        );
        if ($days < 1) {
            $policy["returnPolicyCategory"] = "https://schema.org/MerchantReturnNotPermitted";
            return $policy;
        }
        $free = (bool)DeliveryPolicy::Config()->get(DeliveryPolicy::KEY_FREE_RETURN, false);
        $policy["returnPolicyCategory"] = "https://schema.org/MerchantReturnFiniteReturnWindow";
        $policy["merchantReturnDays"] = $days;
        $policy["returnMethod"] = "https://schema.org/ReturnByMail";
        $policy["returnFees"] = $free ? "https://schema.org/FreeReturn" : "https://schema.org/ReturnFeesCustomerResponsibility";
        return $policy;
    }
}

<?php
include_once("responders/json/JSONResponder.php");
include_once("store/courier/CourierOffices.php");

/**
 * The offices of a courier for the office chooser (OptionFinder) - the same list for every visitor:
 *   ?responder=CourierOfficesResponder&function=list&courier=<id>&v=<last refresh>
 * 'contents' is the JSON list of the options (CourierOffices::Options). The refresh time in the address changes
 * the address, so browsers and the CDN may keep the answer for a day.
 */
class CourierOfficesResponder extends JSONResponder
{
    const int MAX_AGE = 86400;

    public static function EarlyProcessing() : bool
    {
        return true;
    }

    public function _list(JSONResponse $resp) : void
    {
        $courier = (int)($_GET["courier"] ?? 0);
        if (is_null(CourierOffices::Source($courier))) throw new Exception("Unknown courier");
        echo json_encode(CourierOffices::Options($courier), JSON_UNESCAPED_UNICODE);
        $resp->setMaxAge(CourierOfficesResponder::MAX_AGE);
    }
}

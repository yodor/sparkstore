<?php

if (!isset($page) || !($page instanceof CheckoutPage)) throw new Exception("No page defined");

$speedyLocator = new URL("https://services.speedy.bg/office_locator_widget_v3/office_locator.php");
$speedyLocator->add(new URLParameter("lang", "bg"));
$speedyLocator->add(new URLParameter("showOfficesList", "1"));
$speedyLocator->add(new URLParameter("pickUp", "1"));
$speedyLocator->add(new URLParameter("officeType", "OFFICE"));
$speedyLocator->add(new URLParameter("officesFilterOnTheMap", "1"));
$speedyLocator->add(new URLParameter("selectOfficeButtonCaption", "Изберете този офис"));

$iframe = new Component(false);
$iframe->setComponentClass("OfficeLocator");
$iframe->addClassName("Speedy");

$page->base()->items()->append($iframe);



$script = new Script();
$page->base()->items()->append($script);

$script->setContents(<<<JS

window.addEventListener(
    'message',
    function (e) {
        const officeData = e.data;
        document.forms.CourierOffice.value = officeData.fullAddressString;
    }, false);

JS);



    /**
     * {
     * "id": 98,
     * "name": "СОФИЯ - МЛАДОСТ 1 (ЖК)",
     * "nameEn": "SOFIA - MLADOST 1 (ZHK)",
     * "siteId": 68134,
     * "address": {
     * "countryId": 100,
     * "siteId": 68134,
     * "siteType": "ГР.",
     * "siteName": "СОФИЯ",
     * "postCode": "1000",
     * "complexId": 47,
     * "complexType": "ЖК",
     * "complexName": "МЛАДОСТ 1",
     * "blockNo": "12А",
     * "addressNote": "МАГАЗИН 14",
     * "x": 23.376336,
     * "y": 42.65758,
     * "fullAddressString": "гр. СОФИЯ жк МЛАДОСТ 1 бл. 12А МАГАЗИН 14",
     * "siteAddressString": "гр. СОФИЯ",
     * "localAddressString": "жк МЛАДОСТ 1 бл. 12А МАГАЗИН 14"
     * },
     * "workingTimeFrom": "09:00",
     * "workingTimeTo": "19:00",
     * "workingTimeHalfFrom": "09:00",
     * "workingTimeHalfTo": "14:00",
     * "workingTimeDayOffFrom": "00:00",
     * "workingTimeDayOffTo": "00:00",
     * "sameDayDepartureCutoff": "18:30",
     * "sameDayDepartureCutoffHalf": "14:00",
     * "sameDayDepartureCutoffDayOff": "00:00",
     * "maxParcelDimensions": {
     * "width": 80,
     * "height": 180,
     * "depth": 80
     * },
     * "maxParcelWeight": 32,
     * "type": "OFFICE",
     * "nearbyOfficeId": 240,
     * "workingTimeSchedule": [
     * {
     * "date": "2026-02-16",
     * "workingTimeFrom": "09:00",
     * "workingTimeTo": "19:00",
     * "sameDayDepartureCutoff": "18:30",
     * "standardSchedule": true
     * },
     * {
     * "date": "2026-02-17",
     * "workingTimeFrom": "09:00",
     * "workingTimeTo": "19:00",
     * "sameDayDepartureCutoff": "18:30",
     * "standardSchedule": true
     * },
     * {
     * "date": "2026-02-18",
     * "workingTimeFrom": "09:00",
     * "workingTimeTo": "19:00",
     * "sameDayDepartureCutoff": "18:30",
     * "standardSchedule": true
     * },
     * {
     * "date": "2026-02-19",
     * "workingTimeFrom": "09:00",
     * "workingTimeTo": "19:00",
     * "sameDayDepartureCutoff": "18:30",
     * "standardSchedule": true
     * },
     * {
     * "date": "2026-02-20",
     * "workingTimeFrom": "09:00",
     * "workingTimeTo": "19:00",
     * "sameDayDepartureCutoff": "18:30",
     * "standardSchedule": true
     * },
     * {
     * "date": "2026-02-21",
     * "workingTimeFrom": "09:00",
     * "workingTimeTo": "14:00",
     * "sameDayDepartureCutoff": "14:00",
     * "standardSchedule": true
     * },
     * {
     * "date": "2026-02-23",
     * "workingTimeFrom": "09:00",
     * "workingTimeTo": "19:00",
     * "sameDayDepartureCutoff": "18:30",
     * "standardSchedule": true
     * },
     * {
     * "date": "2026-02-24",
     * "workingTimeFrom": "09:00",
     * "workingTimeTo": "19:00",
     * "sameDayDepartureCutoff": "18:30",
     * "standardSchedule": true
     * }
     * ],
     * "validFrom": "2000-01-01",
     * "validTo": "3000-01-01",
     * "cargoTypesAllowed": [
     * "PARCEL"
     * ],
     * "pickUpAllowed": true,
     * "dropOffAllowed": true,
     * "palletOffice": false,
     * "cashPaymentAllowed": true,
     * "cardPaymentAllowed": true
     * }
     */
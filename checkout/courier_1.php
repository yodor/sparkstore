<?php

if (!isset($page) || !($page instanceof CheckoutPage)) throw new Exception("No page defined");

$shopUrl = new URL(Spark::Get(Config::SITE_URL));
$econtLocator = new URL("https://officelocator.econt.com/");
$econtLocator->add(new URLParameter("lang", "bg"));
$econtLocator->add(new URLParameter("shopUrl", $shopUrl->fullURL()));

$iframe = new Component(false);
$iframe->setTagName("iframe");
$iframe->setComponentClass("OfficeLocator");
$iframe->addClassName("Econt");

$iframe->setAttribute("src", $econtLocator->fullURL());
$page->base()->items()->append($iframe);



$script = new Script();
$page->base()->items()->append($script);

$script->setContents(<<<JS

window.addEventListener('message', function(event) {
    if (event.data.office) {
        const officeData = event.data.office;
        //console.log('Selected Office:', event.data.office);
        document.forms.CourierOffice.office.value = officeData.address.fullAddress;
        // Contains: id, code, name, city, address, etc.
    }
});

JS);
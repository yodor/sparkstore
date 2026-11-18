<?php
include_once("store/templates/modules/checkout/content/CheckoutRowEditor.php");
include_once("components/Script.php");

/**
 * Step 3, office delivery: the address of the courier office (CourierAddressesBean - one per client).
 * Econt: the office locator widget fills it; Speedy: the client types it (its office API is a later task).
 */
class CheckoutDeliveryCourier extends CheckoutRowEditor
{
    protected string $baseClass = "item courier_office";

    protected function check() : void
    {
        if (is_null(Cart::Instance()->getDelivery()->getSelectedCourier())) $this->redirect("delivery");
    }

    public function initialize() : void
    {
        parent::initialize();
        if ($this->editID < 1) $this->page()->base()->addClassName("empty");

        $courier = Cart::Instance()->getDelivery()->getSelectedCourier();
        if ($courier->getID() == DeliveryCourier::COURIER_EKONT) $this->econtLocator();
    }

    /**
     * Econt office locator - the selected office address goes into the 'office' field
     * @return void
     */
    protected function econtLocator() : void
    {
        $shopUrl = new URL(Spark::Get(Config::SITE_URL));
        $locator = new URL("https://officelocator.econt.com/");
        $locator->add(new URLParameter("lang", "bg"));
        $locator->add(new URLParameter("shopUrl", $shopUrl->fullURL()));

        $iframe = new Component(false);
        $iframe->setTagName("iframe");
        $iframe->setComponentClass("OfficeLocator");
        $iframe->addClassName("Econt");
        $iframe->setAttribute("src", $locator->fullURL());
        $this->page()->base()->items()->append($iframe);

        $name = json_encode($this->form->getName());
        $script = new Script();
        $script->setContents(<<<JS
window.addEventListener('message', function(event) {
    //only the messages of the locator
    if (event.origin !== 'https://officelocator.econt.com') return;
    if (event.data && event.data.office && event.data.office.address) {
        document.forms[$name].office.value = event.data.office.address.fullAddress;
    }
});
JS);
        $this->page()->base()->items()->append($script);
    }

    protected function saved() : void
    {
        $cart = Cart::Instance();
        $cart->getDelivery()->getSelectedCourier()?->setSelectedOption(DeliveryOption::COURIER_OFFICE);
        $cart->store();
    }

    protected function getContentTitle() : string
    {
        return tr("Избор на офис на куриер за доставка");
    }
}

<?php
include_once("store/templates/modules/checkout/content/CheckoutContent.php");
include_once("store/courier/CourierOffices.php");
include_once("store/responders/json/CourierOfficesResponder.php");
include_once("components/OptionFinder.php");
include_once("components/Container.php");
include_once("components/Component.php");
include_once("utils/url/URL.php");

/**
 * Step 3, office delivery: the office of the selected courier, chosen from its list (CourierOffices - the copy the
 * admin refreshes): type the town, click the office. Only the key of the office is posted - the server takes the
 * office from the list (an unknown key is refused) and keeps it for the client (one per courier).
 *
 *   form[name=CourierOfficeForm] > .OptionFinder[name=office_key]
 */
class CheckoutDeliveryCourier extends CheckoutContent
{
    const string FORM = "CourierOfficeForm";
    const string FIELD = "office_key";

    protected function courier() : DeliveryCourier
    {
        $courier = Cart::Instance()->getDelivery()->getSelectedCourier();
        if (is_null($courier)) $this->redirect("delivery");
        return $courier;
    }

    public function processInput() : void
    {
        parent::processInput();
        if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST" || !isset($_POST[CheckoutDeliveryCourier::FIELD])) return;

        $this->ensureCartItems();
        $courier = $this->courier();
        $office = CourierOffices::Find($courier->getID(), trim((string)$_POST[CheckoutDeliveryCourier::FIELD]));
        if (is_null($office)) $this->redirect("delivery/courier", tr("Моля, изберете офис от списъка"));

        CourierOffices::Choose(Module::Active()->getUserID(), $courier->getID(), $office);

        $cart = Cart::Instance();
        $courier->setSelectedOption(DeliveryOption::COURIER_OFFICE);
        $cart->store();
        $this->redirect("confirm");
    }

    public function initialize() : void
    {
        $this->ensureCartItems();
        $courier = $this->courier();

        $page = $this->page();
        $page->getCartComponent()->setRenderEnabled(false);
        $page->base()->addClassName("item courier_office");

        $form = new Container(false);
        $form->setTagName("form");
        $form->setComponentClass("CourierOffice");
        $form->setAttribute("name", CheckoutDeliveryCourier::FORM);
        $form->setAttribute("method", "post");
        $form->setAttribute("action", Module::PathURL("delivery/courier")->toString());

        $label = new Component(false);
        $label->setTagName("label");
        $label->setComponentClass("caption");
        $label->setText(tr("Офис") . " (" . $courier->getTitle() . ")");
        $form->items()->append($label);

        $status = CourierOffices::Status($courier->getID());
        if ($status["count"] < 1) {
            $empty = new Component(false);
            $empty->setComponentClass("empty");
            $empty->setText(tr("Списъкът с офиси на куриера не е наличен в момента. Моля, свържете се с нас."));
            $form->items()->append($empty);
        }
        else {
            $source = Module::PathURL("delivery/courier");
            $source->add(new URLParameter(JSONResponder::KEY_JSONREQUEST, "1"));
            $source->add(new URLParameter("responder", "CourierOfficesResponder"));
            $source->add(new URLParameter("function", "list"));
            $source->add(new URLParameter("courier", (string)$courier->getID()));
            //a new list - a new address (the answer is kept for a day)
            $source->add(new URLParameter("v", (string)strtotime($status["updated"])));

            $finder = new OptionFinder(CheckoutDeliveryCourier::FIELD);
            $finder->setSource($source);
            $finder->setPlaceholder(tr("Въведете населено място"));
            $finder->setHints(tr("Въведете първите букви на населеното място"), tr("Няма офис в това населено място"));

            $chosen = CourierOffices::Chosen(Module::Active()->getUserID(), $courier->getID());
            if (is_array($chosen)) {
                $place = trim($chosen["post_code"] . " " . $chosen["city"]);
                $finder->setChosen((string)$chosen["office_key"], (string)$chosen["name"], implode(", ", array_filter(array((string)$chosen["address"], $place))));
            }
            $form->items()->append($finder);
        }
        $page->base()->items()->append($form);

        $name = json_encode(CheckoutDeliveryCourier::FORM);
        $this->navigation(CheckoutContent::NAV_LEFT, tr("Назад"), "edit", $this->backURL());
        $this->navigation(CheckoutContent::NAV_RIGHT, tr("Продължи"), "checkout", "javascript:document.forms[$name].submit();");
    }

    protected function getContentTitle() : string
    {
        return tr("Избор на офис на куриер за доставка");
    }
}

<?php
include_once("store/features/shop/templates/modules/checkout/content/CheckoutContent.php");
include_once("store/forms/DeliveryCourierForm.php");
include_once("forms/processors/FormProcessor.php");
include_once("forms/renderers/FormRenderer.php");

/**
 * Step 1: the courier (the couriers enabled in the admin delivery options). Nothing is chosen automatically.
 * Next: the delivery option, or confirm when the courier has it already.
 */
class CheckoutDelivery extends CheckoutContent
{
    protected ?InputForm $form = null;

    public function initialize() : void
    {
        $this->ensureCartItems();
        $page = $this->page();
        $page->getCartComponent()->setRenderEnabled(false);

        $this->form = new DeliveryCourierForm();
        $this->form->setName("DeliveryCourier");
        $courier = Cart::Instance()->getDelivery()->getSelectedCourier();
        if (!is_null($courier)) {
            $this->form->getInput("delivery_courier")->setValue($courier->getID());
        }

        $renderer = new FormRenderer($this->form);
        $renderer->getSubmitLine()->setRenderEnabled(false);
        $page->base()->addClassName("delivery_courier");
        $page->base()->items()->append($renderer);

        $this->navigation(CheckoutContent::NAV_LEFT, tr("Back"), "edit", $this->backURL());
        $this->navigation(CheckoutContent::NAV_RIGHT, tr("Continue"), "checkout", "javascript:document.forms.DeliveryCourier.submit();");
    }

    public function processInput() : void
    {
        parent::processInput();

        $proc = new FormProcessor();
        $proc->process($this->form);
        if ($proc->getStatus() === IFormProcessor::STATUS_ERROR) {
            Session::SetAlert($proc->getMessage());
            return;
        }
        if ($proc->getStatus() !== IFormProcessor::STATUS_OK) return;

        $value = $this->form->getInput("delivery_courier")->getValue();
        $courierID = (int)(is_array($value) ? ($value[0] ?? 0) : $value);

        $cart = Cart::Instance();
        $cart->getDelivery()->setSelectedCourier($courierID);
        $cart->store();
        if (is_null($cart->getDelivery()->getSelectedCourier())) {
            Session::SetAlert(tr("Choose a delivery courier"));
            return;
        }
        $this->nextStep("delivery/option");
    }

    protected function getContentTitle() : string
    {
        return tr("Choose a courier");
    }
}

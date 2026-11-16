<?php
include_once("store/templates/content/CheckoutContent.php");
include_once("store/forms/DeliveryAddressForm.php");
include_once("forms/processors/FormProcessor.php");
include_once("forms/renderers/FormRenderer.php");

/**
 * Step 2: delivery to the door (the registered address) or to a courier office - the options of the selected
 * courier enabled in the admin. Next: the address (or confirm when it is saved already); the office always - the
 * saved office is not bound to a courier, the client checks it.
 */
class CheckoutDeliveryOption extends CheckoutContent
{
    protected ?InputForm $form = null;

    public function initialize() : void
    {
        $this->ensureCartItems();
        $courier = Cart::Instance()->getDelivery()->getSelectedCourier();
        if (is_null($courier)) $this->redirect("delivery");

        $page = $this->page();
        $page->getCartComponent()->setRenderEnabled(false);

        $this->form = new DeliveryAddressForm();
        $this->form->setName("DeliveryAddress");
        $option = $courier->getSelectedOption();
        if (!is_null($option)) {
            $this->form->getInput("delivery_option")->setValue($option->getID());
        }

        $renderer = new FormRenderer($this->form);
        $renderer->getSubmitLine()->setRenderEnabled(false);
        $page->base()->addClassName("delivery_address");
        $page->base()->items()->append($renderer);

        $this->navigation(CheckoutTemplatePage::NAV_LEFT, tr("Назад"), "edit", $this->backURL());
        $this->navigation(CheckoutTemplatePage::NAV_RIGHT, tr("Продължи"), "checkout", "javascript:document.forms.DeliveryAddress.submit();");
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

        $value = $this->form->getInput("delivery_option")->getValue();
        $optionID = (int)(is_array($value) ? ($value[0] ?? 0) : $value);

        $cart = Cart::Instance();
        $courier = $cart->getDelivery()->getSelectedCourier();
        $courier->setSelectedOption($optionID);
        $cart->store();

        $option = $courier->getSelectedOption();
        if (is_null($option)) {
            Session::SetAlert(tr("Изберете начин на доставка"));
            return;
        }
        if ($option->getID() == DeliveryOption::COURIER_OFFICE) $this->redirect("delivery/courier");
        $this->nextStep("delivery/address");
    }

    protected function getContentTitle() : string
    {
        return tr("Начин на доставка");
    }
}

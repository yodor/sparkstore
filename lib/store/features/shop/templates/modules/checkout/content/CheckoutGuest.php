<?php
include_once("store/features/shop/templates/modules/checkout/content/CheckoutContent.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/mailers/FastOrderAdminMailer.php");
include_once("forms/processors/FormProcessor.php");
include_once("forms/renderers/FormRenderer.php");
include_once("features/Features.php");
include_once("templates/Module.php");
include_once("utils/url/URL.php");

/**
 * The 'guest' path of the checkout (the module loginPath) - a guest at confirm comes here: a fast order (name and
 * phone - only a mail to the shop, no delivery; when the fast_order feature is enabled) or the regular order (the
 * account login, then back to confirm).
 * A logged in client goes to confirm.
 */
class CheckoutGuest extends CheckoutContent
{
    protected ?ClientAddressInputForm $form = null;

    public function initialize() : void
    {
        $this->ensureCartItems();

        if (Module::Active()->getAuthContext()) $this->redirect("confirm");

        //after the account login - back to confirm (AccountLogin)
        Session::Set("login.redirect", Module::PathURL("confirm")->toString());

        $page = $this->page();
        $page->getCartComponent()->setRenderEnabled(false);

        if (Features::Enabled("fast_order")) {
            $this->form = new ClientAddressInputForm(true);
            $renderer = new FormRenderer($this->form);

            $section = new OrderSection(tr("Fast order"), "fast_order");
            $note = new Component(false);
            $note->addClassName("note");
            $note->setContents(Spark::AttributeValue(tr("Enter only your name and phone.")) . "<BR><BR>" .
                Spark::AttributeValue(tr("Our consultant will call you within a few minutes to agree the delivery address, the payment and all the details of your order.")));
            $section->value()->items()->append($note);
            $section->value()->items()->append($renderer);
            $section->button()->setRenderEnabled(false);
            $page->base()->items()->append($section);
        }

        $section = new OrderSection(tr("Regular order"), "login");
        $note = new Component(false);
        $note->addClassName("note");
        $note->setText(tr("Log in to your account or register."));
        $section->value()->items()->append($note);
        $section->button()->setContents(tr("Continue"));
        $login = Module::URL("account", "/login") ?? new URL(Spark::Get(Config::LOCAL) . "/account/login");
        $section->button()->setAttribute("href", $login->toString());
        $page->base()->items()->append($section);
    }

    public function processInput() : void
    {
        parent::processInput();
        if (is_null($this->form)) return;

        $proc = new FormProcessor();
        $proc->process($this->form);
        if ($proc->getStatus() === IFormProcessor::STATUS_ERROR) {
            Session::SetAlert($proc->getMessage());
            return;
        }
        if ($proc->getStatus() !== IFormProcessor::STATUS_OK) return;

        $cart = Cart::Instance();
        if ($cart->itemsCount() < 1) $this->redirect("cart", tr("Your cart is empty"));
        if (!$this->form->isFastOrder()) throw new Exception("Incorrect InputForm - fast_order flag not set");

        //the order is only this mail - keep the cart when it did not go out
        $mailer = new FastOrderAdminMailer($this->form);
        if (!$mailer->send()) {
            Session::SetAlert(tr("The order was not sent. Please try again later or call us."));
            return;
        }
        $cart->clear();
        $cart->store();
        Session::Remove("checkout.orderID");
        $this->redirect("complete");
    }

    protected function getContentTitle() : string
    {
        return tr("Customers");
    }
}

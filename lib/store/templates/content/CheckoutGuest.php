<?php
include_once("store/templates/content/CheckoutContent.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/mailers/FastOrderAdminMailer.php");
include_once("forms/processors/FormProcessor.php");
include_once("forms/renderers/FormRenderer.php");

/**
 * The 'guest' path of the checkout (the module loginPath) - a guest at confirm comes here: a fast order (name and
 * phone - only a mail to the shop, no delivery) or the regular order (the account login, then back to confirm).
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

        $this->form = new ClientAddressInputForm(true);
        $renderer = new FormRenderer($this->form);

        $section = new OrderSection(tr("Бърза поръчка"), "fast_order");
        $note = new Component(false);
        $note->addClassName("note");
        $note->setContents(Spark::AttributeValue(tr("Въведете само вашето име и телефон.")) . "<BR><BR>" .
            Spark::AttributeValue(tr("Наш консултант ще ви се обади в рамките на няколко минути, за да уточним адреса за доставка, начина на плащане и всички детайли по вашата поръчка.")));
        $section->value()->items()->append($note);
        $section->value()->items()->append($renderer);
        $section->button()->setRenderEnabled(false);
        $page->base()->items()->append($section);

        $section = new OrderSection(tr("Нормална поръчка"), "login");
        $note = new Component(false);
        $note->addClassName("note");
        $note->setText(tr("Влезте в акаунта си или се регистрирайте."));
        $section->value()->items()->append($note);
        $section->button()->setContents(tr("Continue"));
        $section->button()->setAttribute("href", Spark::Get(Config::LOCAL) . "/account/login");
        $page->base()->items()->append($section);
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

        $cart = Cart::Instance();
        if ($cart->itemsCount() < 1) $this->redirect("cart", tr("Вашата кошница е празна"));
        if (!$this->form->isFastOrder()) throw new Exception("Incorrect InputForm - fast_order flag not set");

        //the order is only this mail - keep the cart when it did not go out
        $mailer = new FastOrderAdminMailer($this->form);
        if (!$mailer->send()) {
            Session::SetAlert(tr("Поръчката не беше изпратена. Моля, опитайте отново по-късно или ни се обадете."));
            return;
        }
        $cart->clear();
        $cart->store();
        Session::Remove("checkout.orderID");
        $this->redirect("complete");
    }

    protected function getContentTitle() : string
    {
        return tr("Клиенти");
    }
}

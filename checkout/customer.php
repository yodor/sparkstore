<?php
include_once("session.php");
include_once("class/pages/CheckoutPage.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/mailers/FastOrderAdminMailer.php");

class FastOrderProcessor extends FormProcessor {
    public function __construct()
    {
        parent::__construct();
    }
    protected function processImpl(InputForm $form) : void
    {
        parent::processImpl($form);

        $cart = Cart::Instance();

        if ($cart->itemsCount()<1) throw new Exception(tr("Your shopping cart is empty"));

        if ($form instanceof ClientAddressInputForm) {
            if ($form->isFastOrder()) {
                $mailer = new FastOrderAdminMailer($form);
                $mailer->send();
                $cart->clear();
                $cart->store();
                header("Location: complete.php");
                exit;
            }
            else {
                throw new Exception("Incorrect InputForm class - fast_order flag not set");
            }
        }

        throw new Exception("Incorrect InputForm class - expecting 'ClientAddressInputForm'");

    }
}

$page = new CheckoutPage();
$page->ensureCartItems();

if ($page->getUserID() > 0) {
    header("Location: confirm.php");
    exit;
}
else {
    Session::Set("login.redirect", Spark::Get(Config::LOCAL)."/checkout/confirm.php");
//    header("Location: ".LOCAL."/account/login.php");
//    exit;
}

$form = new ClientAddressInputForm(true);
$frend = new FormRenderer($form);
$proc = new FastOrderProcessor();
$proc->process($form);
if ($proc->getStatus()==IFormProcessor::STATUS_ERROR) {
    Session::SetAlert($proc->getMessage());
}

$page->setTitle(tr("Клиенти"));
$page->getCartComponent()->setRenderEnabled(false);

$section = new OrderSection(tr("Бърза поръчка"), "fast_order");
$note = new Component(false);
$note->addClassName("note");
$note->buffer()->start();
echo tr("Въведете само вашето име и телефон.");
echo "<BR><BR>";
echo tr("Наш консултант ще ви се обади в рамките на няколко минути, за да уточним адреса за доставка, начина на плащане и всички детайли по вашата поръчка.");
$note->buffer()->end();

$section->value()->items()->append($note);
$section->value()->items()->append($frend);
$section->button()->setRenderEnabled(false);
$page->base()->items()->append($section);

$section = new OrderSection(tr("Нормална поръчка"),"login");

$note = new Component(false);
$note->addClassName("note");
$note->buffer()->start();
echo tr("Влезте в акаунта си или се регистрирайте.");
//echo "<BR><BR>";
//echo tr("Това гарантира сигурна обработка на Вашите данни и поръчка.");
$note->buffer()->end();

$section->value()->items()->append($note);
$section->button()->setContents(tr("Continue"));
$section->button()->setAttribute("href", Spark::Get(Config::LOCAL)."/account/login.php");
$page->base()->items()->append($section);

$page->render();
?>

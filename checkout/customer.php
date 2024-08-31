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
    Session::Set("login.redirect", LOCAL."/checkout/confirm.php");
//    header("Location: ".LOCAL."/account/login.php");
//    exit;
}

$form = new ClientAddressInputForm(true);
$frend = new FormRenderer($form);
$proc = new FastOrderProcessor();
$proc->process($form);

$page->setTitle(tr("Fast Order"));

$page->startRender();

echo "<div class='columns'>";

echo "<div class='column fast_order'>";

    echo "<h1 class='Caption'>".tr("Бърза поръчка")."</h1>";

    echo "<div class='panel'>";
    $frend->render();
    echo "</div>";

echo "</div>"; //column


echo "<div class='column login'>"; //register

    echo "<h1 class='Caption'>".tr("Вече имате профил?")."</h1>";

    echo "<div class='panel'>";
    echo "<a class='ColorButton' href='".LOCAL."/account/login.php'>".tr("Login")."</a>";
    echo "</div>";

echo "</div>"; //column

echo "<div class='column register'>"; //register

    echo "<h1 class='Caption'>" . tr("Все още нямате профил ?") . "</h1>";

    echo "<div class='panel'>";
    echo "<a class='ColorButton' href='".LOCAL."/account/register.php'>".tr("Регистрация")."</a>";
    echo "</div>"; //panel

echo "</div>"; //column

echo "</div>";//columns

$page->finishRender();

?>

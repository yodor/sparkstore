<?php
include_once("session.php");

include_once("class/pages/CheckoutPage.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/beans/ClientAddressesBean.php");
include_once("store/forms/InvoiceDetailsInputForm.php");
include_once("store/beans/InvoiceDetailsBean.php");
include_once("store/forms/CourierOfficeInputForm.php");
include_once("store/beans/CourierAddressesBean.php");

include_once("store/utils/OrderProcessor.php");
include_once("store/mailers/OrderConfirmationMailer.php");
include_once("store/mailers/OrderConfirmationAdminMailer.php");

class RequireInvoiceInputForm extends InputForm
{
    public function __construct()
    {
        parent::__construct();
        $input = DataInputFactory::Create(DataInputFactory::CHECKBOX, "require_invoice", "Да се издаде фактура", 0);

        $input->getRenderer()->getItemRenderer()->setAttribute("onClick", "javascript:this.form.submit()");
        $this->addInput($input);
    }
}

class RequireInvoiceFormProcessor extends FormProcessor
{

    public function processImpl(InputForm $form) : void
    {

        parent::processImpl($form);

        if ($this->getStatus() != IFormProcessor::STATUS_OK) return;

        $page = SparkPage::Instance();

        $cart = Cart::Instance();
        $cart->setRequireInvoice($form->getInput("require_invoice")->getValue());
        $cart->store();

        $cabrow = $this->bean->getResult("userID", $page->getUserID());
        if (!$cabrow) {
            header("Location: invoice_details.php");
            exit;
        }




    }

}

class OrderNoteInputForm extends InputForm
{
    public function __construct()
    {
        parent::__construct();
        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "note", "Бележка", 0);
        $field->getRenderer()->input()?->setAttribute("maxlength", "200");
        $this->addInput($field);
    }
}

class OrderNoteFormProcessor extends FormProcessor
{
    public function processImpl(InputForm $form) : void
    {
        parent::processImpl($form);

        if ($this->getStatus() != IFormProcessor::STATUS_OK) return;

        $cart = Cart::Instance();
        $cart->setNote($form->getInput("note")->getValue());
        $cart->store();

        header("Location: finalize.php");
        exit;
    }
}

$page = new CheckoutPage();

$page->ensureCartItems();
$page->ensureClient();

$cart = Cart::Instance();

$courier = $cart->getDelivery()->getSelectedCourier();
if (is_null($courier)) {
    header("Location: delivery.php");
    exit;
}
else {
    if (is_null($courier->getSelectedOption())) {
        header("Location: delivery_option.php");
        exit;
    }
}


//request invoice
$reqform = new RequireInvoiceInputForm();

$idb = new InvoiceDetailsBean();
$idbrow = $idb->getResult("userID", $page->getUserID());
if (!$idbrow) {
    $reqform->getInput("require_invoice")->setValue(false);
    $cart->setRequireInvoice(false);
}
else {
    $reqform->getInput("require_invoice")->setValue($cart->getRequireInvoice());
}

$reqproc = new RequireInvoiceFormProcessor();
$reqproc->setBean($idb);

$frend = new FormRenderer($reqform);
$frend->getSubmitLine()->setEnabled(false);
$reqproc->process($reqform);


//order note
$noteform = new OrderNoteInputForm();
$noteform->getInput("note")->setValue($cart->getNote());

$nfrend = new FormRenderer($noteform);
$nfrend->getSubmitLine()->setEnabled(false);

$noteproc = new OrderNoteFormProcessor();
$noteproc->process($noteform);

$page->setTitle(tr("Потвърди поръчка"));

$page->startRender();


$page->drawCartItems();

echo "<div class='item delivery_courier'>";

echo "<h1 class='Caption'>" . tr("Куриер за доставка") . "</h1>";

echo "<div class='value'>";
echo tr($cart->getDelivery()->getSelectedCourier()->getTitle());
echo "</div>";

echo "<a class='ColorButton' href='delivery.php'>";
echo tr("Промени");
echo "</a>";

echo "</div>"; //item delivery_type


echo "<div class='item delivery_type'>";

echo "<h1 class='Caption'>" . tr("Начин на доставка") . "</h1>";

echo "<div class='value'>";
echo tr($cart->getDelivery()->getSelectedCourier()->getSelectedOption()->getTitle());
echo "</div>";

echo "<a class='ColorButton' href='delivery_option.php'>";
echo tr("Промени");
echo "</a>";

echo "</div>"; //item delivery_type

echo "<div class='item address'>";

echo "<h1 class='Caption'>" . tr("Адрес за доставка") . "</h1>";

echo "<div class='value'>";
$option = $cart->getDelivery()->getSelectedCourier()->getSelectedOption();
if ($option->getID() == DeliveryOption::USER_ADDRESS) {
    $form = new ClientAddressInputForm();
    $bean = new ClientAddressesBean();
    $row = $bean->getResult("userID", $page->getUserID());
    if (!$row) {
        header("Location: delivery_address.php");
        exit;
    }

    $form->loadBeanData($row[$bean->key()], $bean);
    $form->renderPlain();

    echo "<a class='ColorButton' href='delivery_address.php'>";
    echo tr("Промени");
    echo "</a>";

}
else if ($option->getID() == DeliveryOption::COURIER_OFFICE) {
    $form = new CourierOfficeInputForm();
    $bean = new CourierAddressesBean();
    $form->getInput("office")->setLabel("Офис на куриер");
    $row = $bean->getResult("userID", $page->getUserID());
    if (!$row) {
        header("Location: delivery_courier.php");
        exit;
    }

    $form->loadBeanData($row[$bean->key()], $bean);
    $form->renderPlain();

    echo "<a class='ColorButton' href='delivery_courier.php'>";
    echo tr("Промени");
    echo "</a>";
}
echo "</div>";//value

echo "</div>";// item address

echo "<div class='item invoicing'>";

echo "<h1 class='Caption'>" . tr("Фактуриране") . "</h1>";

$frend->render();

echo "<div class='value'>";
if ($idbrow && $cart->getRequireInvoice()) {
    $idform = new InvoiceDetailsInputForm();
    $idform->loadBeanData($idbrow[$idb->key()], $idb);
    $idform->renderPlain();

    echo "<a class='ColorButton' href='invoice_details.php'>";
    echo tr("Промени");
    echo "</a>";
}
echo "</div>";

echo "</div>";

echo "<div class='item note'>";
echo "<h1 class='Caption'>" . tr("Бележка към поръчката") . "</h1>";
$nfrend->render();
echo "</div>";


$action = $page->getAction(CheckoutPage::NAV_LEFT);
$action->setTitle(tr("Назад"));
$action->setClassName("edit");
$action->getURL()->fromString("cart.php");

$cmp = $page->getNavigation()->items()->getByName(CheckoutPage::NAV_CENTER);
if ($cmp instanceof ClosureComponent) {
    $render = function (ClosureComponent $cmp) {
        echo "<div class='note'>";
        echo "<i>" . tr("Натискайки бутона 'Потвърди поръчка' Вие се съгласявате с нашите") . "&nbsp;" . "<a  href='" . LOCAL . "/pages/index.php?class=terms'>" . tr("Условия за ползване") . "</a></i>";
        echo "</div>";
    };
    $cmp->setClosure($render);
}

$action = $page->getAction(CheckoutPage::NAV_RIGHT);
$action->setTitle(tr("Потвърди поръчка"));
$action->setClassName("checkout");
$action->getURL()->fromString("javascript:document.forms.OrderNoteInputForm.submit()");


$page->renderNavigation();


Session::set("checkout.navigation.back",  URL::Current()->toString());

$page->finishRender();

?>

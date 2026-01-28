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

        $input->getRenderer()->getItemRenderer()->setAttribute("onClick", "javascript:document.forms.RequireInvoiceInputForm.submit()");
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
$frend->getSubmitLine()->setRenderEnabled(false);
$reqproc->process($reqform);


//order note
$noteform = new OrderNoteInputForm();
$noteform->getInput("note")->setValue($cart->getNote());

$nfrend = new FormRenderer($noteform);
$nfrend->getSubmitLine()->setRenderEnabled(false);

$noteproc = new OrderNoteFormProcessor();
$noteproc->process($noteform);


$page->setTitle(tr("Потвърди поръчка"));

///
/// OrderSections
///


//delivery_courier
$section = new OrderSection(tr("Куриер за доставка"), "delivery_courier");
$section->value()->setContents($cart->getDelivery()->getSelectedCourier()->getTitle());
$section->button()->setAttribute("href", "delivery.php");
$page->base()->items()->append($section);

//delivery_type
$section = new OrderSection(tr("Начин на доставка"), "delivery_type");
$section->value()->setContents($cart->getDelivery()->getSelectedCourier()->getSelectedOption()->getTitle());
$section->button()->setAttribute("href", "delivery_option.php");
$page->base()->items()->append($section);

//address
$section = new OrderSection(tr("Адрес за доставка"), "address");

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

    $section->value()->items()->append($form->CreatePlainRenderer());
    $section->button()->setAttribute("href", "delivery_address.php");
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

    $section->value()->items()->append($form->CreatePlainRenderer());
    $section->button()->setAttribute("href", "delivery_courier.php");
}
$page->base()->items()->append($section);

//invoicing
$section = new OrderSection(tr("Фактуриране"), "invoicing");
$section->value()->items()->append($frend);

if ($idbrow && $cart->getRequireInvoice()) {
    $idform = new InvoiceDetailsInputForm();
    $idform->loadBeanData($idbrow[$idb->key()], $idb);
    $section->value()->items()->append($idform->CreatePlainRenderer());
}
$section->button()->setAttribute("href", "invoice_details.php");
$page->base()->items()->append($section);

//order note
$section = new OrderSection(tr("Бележка към поръчката"), "note");
$section->value()->items()->append($nfrend);
$section->button()->setRenderEnabled(false);
$page->base()->items()->append($section);


$note = new Component(false);
$note->setComponentClass("acceptNote");
$note->setContents("<i>" . tr("Натискайки бутона 'Потвърди поръчка' Вие се съгласявате с нашите") . "&nbsp;" . "<a  href='" . LOCAL . "/pages/index.php?class=terms'>" . tr("Условия за ползване") . "</a></i>");
$page->base()->items()->append($note);


$page->initialize();

$action = $page->getAction(CheckoutPage::NAV_LEFT);
$action->setContents(tr("Назад"));
$action->setClassName("edit");
$action->getURL()->fromString("cart.php");


$action = $page->getAction(CheckoutPage::NAV_CENTER);
$action->setClassName("disabled");

$action = $page->getAction(CheckoutPage::NAV_RIGHT);
$action->setContents(tr("Потвърди поръчка"));
$action->setClassName("checkout");
$action->getURL()->fromString("javascript:document.forms.OrderNoteInputForm.submit()");


Session::set("checkout.navigation.back",  URL::Current()->toString());

$page->render();

?>

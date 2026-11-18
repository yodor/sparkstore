<?php
include_once("store/templates/modules/checkout/content/CheckoutContent.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/forms/InvoiceDetailsInputForm.php");
include_once("store/forms/CourierOfficeInputForm.php");
include_once("forms/InputForm.php");
include_once("forms/processors/FormProcessor.php");
include_once("forms/renderers/FormRenderer.php");
include_once("input/DataInputFactory.php");
include_once("utils/url/DynamicPageURL.php");

/**
 * Invoice wanted - the checkbox submits its form
 */
class RequireInvoiceInputForm extends InputForm
{
    public function __construct()
    {
        parent::__construct();
        $input = DataInputFactory::Create(InputType::CHECKBOX, "require_invoice", "Да се издаде фактура", 0);
        $input->getRenderer()->getItemRenderer()->setAttribute("onClick", "javascript:document.forms.RequireInvoiceInputForm.submit()");
        $input->getRenderer()->getItemRenderer()->getInput()->setAttribute("autocomplete", "off");
        $this->addInput($input);
    }
}

/**
 * Note of the order - sent with the order (finalize)
 */
class OrderNoteInputForm extends InputForm
{
    public function __construct()
    {
        parent::__construct();
        $field = DataInputFactory::Create(InputType::TEXTAREA, "note", "Бележка", 0);
        $field->getRenderer()->input()?->setAttribute("maxlength", "200");
        $this->addInput($field);
    }
}

/**
 * The cashier: every step of the order data must be filled - the first missing one is opened
 * (CheckoutContent::MissingStep). Then all of it with a 'change' button per section, the cart with the delivery
 * and the order button (finalize, POST).
 */
class CheckoutConfirm extends CheckoutContent
{
    protected ?RequireInvoiceInputForm $invoiceForm = null;

    public function initialize() : void
    {
        $this->ensureCartItems();

        //back from the account login - not needed any more
        Session::Remove("login.redirect");

        $cart = Cart::Instance();
        $userID = Module::Active()->getUserID();

        //no invoice details saved - no invoice (the checkbox opens the invoice step)
        if (!CheckoutContent::HaveRow(new InvoiceDetailsBean(), $userID) && $cart->getRequireInvoice()) {
            $cart->setRequireInvoice(false);
            $cart->store();
        }

        $missing = CheckoutContent::MissingStep($userID);
        if ($missing !== "") $this->redirect($missing);

        Session::Set("checkout.navigation.back", Module::PathURL("confirm")->toString());

        $page = $this->page();
        $courier = $cart->getDelivery()->getSelectedCourier();
        $option = $courier->getSelectedOption();

        $section = new OrderSection(tr("Куриер за доставка"), "delivery_courier");
        $section->value()->setText($courier->getTitle());
        $section->button()->setAttribute("href", Module::PathURL("delivery")->toString());
        $page->base()->items()->append($section);

        $section = new OrderSection(tr("Начин на доставка"), "delivery_type");
        $section->value()->setText($option->getTitle());
        $section->button()->setAttribute("href", Module::PathURL("delivery/option")->toString());
        $page->base()->items()->append($section);

        $section = new OrderSection(tr("Адрес за доставка"), "address");
        if ($option->getID() == DeliveryOption::USER_ADDRESS) {
            $section->value()->items()->append($this->rowView(new ClientAddressInputForm(), new ClientAddressesBean(), $userID));
            $section->button()->setAttribute("href", Module::PathURL("delivery/address")->toString());
        }
        else {
            $form = new CourierOfficeInputForm();
            $form->getInput("office")->setLabel("Офис на куриер");
            $section->value()->items()->append($this->rowView($form, new CourierAddressesBean(), $userID));
            $section->button()->setAttribute("href", Module::PathURL("delivery/courier")->toString());
        }
        $page->base()->items()->append($section);

        $this->invoiceForm = new RequireInvoiceInputForm();
        $this->invoiceForm->getInput("require_invoice")->setValue($cart->getRequireInvoice());
        $invoiceRenderer = new FormRenderer($this->invoiceForm);
        $invoiceRenderer->getSubmitLine()->setRenderEnabled(false);

        $section = new OrderSection(tr("Фактуриране"), "invoicing");
        $section->value()->items()->append($invoiceRenderer);
        if ($cart->getRequireInvoice()) {
            $section->value()->items()->append($this->rowView(new InvoiceDetailsInputForm(), new InvoiceDetailsBean(), $userID));
        }
        $section->button()->setAttribute("href", Module::PathURL("invoice")->toString());
        $page->base()->items()->append($section);

        //the note goes with the order button to finalize
        $noteForm = new OrderNoteInputForm();
        $noteForm->getInput("note")->setValue($cart->getNote());
        $noteRenderer = new FormRenderer($noteForm);
        $noteRenderer->setAttribute("action", Module::PathURL("finalize")->toString());
        $noteRenderer->getSubmitLine()->setRenderEnabled(false);

        $section = new OrderSection(tr("Бележка към поръчката"), "note");
        $section->value()->items()->append($noteRenderer);
        $section->button()->setRenderEnabled(false);
        $page->base()->items()->append($section);

        $termsURL = DynamicPageURL::Group("terms");
        $note = new Component(false);
        $note->setComponentClass("acceptNote");
        $note->setContents("<i>" . Spark::AttributeValue(tr("Натискайки бутона 'Потвърди поръчка' Вие се съгласявате с нашите")) . "&nbsp;" .
            "<a href='" . Spark::AttributeValue($termsURL->fullURL()->toString()) . "'>" . Spark::AttributeValue(tr("Условия за ползване")) . "</a></i>");
        $page->base()->items()->append($note);

        //the cart for context - with the delivery of the chosen option
        $page->getCartComponent()->initialize();

        $this->navigation(CheckoutContent::NAV_LEFT, tr("Назад"), "edit", Module::PathURL("cart")->toString());
        $this->navigation(CheckoutContent::NAV_RIGHT, tr("Потвърди поръчка"), "checkout", "javascript:document.forms.OrderNoteInputForm.submit()");
    }

    /**
     * Plain view of the client's row of '$bean' in '$form'
     */
    protected function rowView(InputForm $form, DBTableBean $bean, int $userID) : Component
    {
        $row = $bean->getResult("userID", (string)$userID, $bean->key());
        if (is_array($row)) $form->loadBeanData((int)$row[$bean->key()], $bean);
        return $form->CreatePlainRenderer();
    }

    public function processInput() : void
    {
        parent::processInput();

        $proc = new FormProcessor();
        $proc->process($this->invoiceForm);
        if ($proc->getStatus() === IFormProcessor::STATUS_ERROR) {
            Session::SetAlert($proc->getMessage());
            return;
        }
        if ($proc->getStatus() !== IFormProcessor::STATUS_OK) return;

        $require = (bool)$this->invoiceForm->getInput("require_invoice")->getValue();
        $cart = Cart::Instance();
        $cart->setRequireInvoice($require);
        $cart->store();

        if ($require && !CheckoutContent::HaveRow(new InvoiceDetailsBean(), Module::Active()->getUserID())) {
            $this->redirect("invoice");
        }
        $this->redirect("confirm");
    }

    protected function getContentTitle() : string
    {
        return tr("Потвърди поръчка");
    }
}

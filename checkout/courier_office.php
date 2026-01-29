<?php
if (!isset($courier)) exit;
if (!isset($page)) exit;

if (!$page instanceof CheckoutPage) exit;
if (!$courier instanceof DeliveryCourier) exit;

include_once("store/forms/CourierOfficeInputForm.php");
include_once("store/beans/CourierAddressesBean.php");

class OfficeFormProcessor extends FormProcessor
{


    public function processImpl(InputForm $form) : void
    {
        parent::processImpl($form);

        if ($this->getStatus() != IFormProcessor::STATUS_OK) return;

        $page = StorePage::Instance();

        $dbt = new BeanTransactor($this->bean, $this->editID);
        $dbt->appendValue("userID", $page->getUserID());

        $dbt->processForm($form);

        //will do insert or update
        $dbt->processBean();


        Cart::Instance()->getDelivery()->getSelectedCourier()->setSelectedOption(DeliveryOption::COURIER_OFFICE);

        header("Location: confirm.php");
        exit;
    }
}

$bean = new CourierAddressesBean();
$proc = new OfficeFormProcessor();
$proc->setBean($bean);

$form = new CourierOfficeInputForm();
$form->setName("CourierOffice");

$empty = "";
$eorow = $bean->getResult("userID", $page->getUserID());
if (!$eorow) {
    $empty = "empty";
}
else {
    $editID = (int)$eorow[$bean->key()];
    $proc->setEditID($editID);
    $form->loadBeanData($editID, $bean);
}

$frend = new FormRenderer($form);

$proc->process($form);

$page->setTitle(tr("Избор на офис на куриер за доставка"));

$frend->getSubmitLine()->setRenderEnabled(false);
$page->base()->items()->append($frend);

$page->base()->setClassName("item ekont_office $empty");

//$page->initialize();
$page->getCartComponent()->setRenderEnabled(false);

$back_url = Session::get("checkout.navigation.back", "delivery.php");

$action = $page->getAction(CheckoutPage::NAV_LEFT);
$action->setContents(tr("Назад"));
$action->setClassName("edit");
$action->getURL()->fromString($back_url);

$action = $page->getAction(CheckoutPage::NAV_RIGHT);
$action->setContents(tr("Продължи"));
$action->setClassName("checkout");
$action->getURL()->fromString("javascript:document.forms.CourierOffice.submit()");

$page->render();

?>

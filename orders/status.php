<?php
include_once("session.php");
include_once("class/pages/ProductsPage.php");

include_once("store/beans/OrdersBean.php");

include_once("forms/processors/FormProcessor.php");
include_once("forms/renderers/FormRenderer.php");

include_once("store/forms/OrderStatusInputForm.php");
include_once("store/forms/OrderAddressInputForm.php");

class OrderStatusProcessor extends FormProcessor
{
    public $order = NULL;
    public $orderID = -1;
    public $confirm_ticket = "";

    protected function processImpl(InputForm $form) : void
    {

        parent::processImpl($form);

        if ($this->status != IFormProcessor::STATUS_OK) return;

        $orders = new OrdersBean();

        $ticket = $form->getInput("ticket")->getValue();
        $email = $form->getInput("email")->getValue();

        $qry = $orders->query();
        $qry->select->where()->add("order_identifier", "'$ticket'")->add("client_identifier", "'$email'");
        $qry->select->limit = " 1 ";

        if ($qry->exec() > 0 && $order_row = $qry->next()) {

            $this->order = $order_row;
            $this->orderID = $order_row[$orders->key()];
            $this->confirm_ticket = $ticket;

        }
        else {
            throw new Exception(tr("Поръчката с този код не е намерена. Моля, уверете се, че сте въвели правилно вашият код на поръчка и email."));
        }
    }
}

$page = new ProductsPage();

$form = new OrderStatusInputForm();

$tfr = new FormRenderer($form);
$tfr->setName("OrderStatus");

$proc = new OrderStatusProcessor();

$form->setProcessor($proc);

$proc->process($form);

if ($proc->getStatus() == IFormProcessor::STATUS_ERROR) {

    Session::SetAlert(tr($form->getProcessor()->getMessage()));

}

$page->startRender();

echo "<div class='column left'>";
$page->renderCategoryTree();
$page->renderNewsItems();

echo "</div>";

echo "<div class='column right orders'>";

if ($proc->getStatus() == IFormProcessor::STATUS_OK) {

    echo "<div class='Caption'>";
    echo tr("Състояние на поръчка");
    echo "</div>";

    $page->renderOrderDetails($proc->orderID, $proc->order, $proc->confirm_ticket);

    $page->renderOrderDeliveryAddress($proc->order);

}
else {

    echo "<div class='Caption'>";
    echo tr("Състояние на поръчка");
    echo "</div>";

    echo "<div class='panel'>";

    echo tr("Тук може да проверите състоянието на вашата поръчка");
    echo "<BR>";
    echo tr("Въведете Вашият email и код за потвърждение за да продължите.");
    echo "<BR>";

    $tfr->renderForm($form);

    echo "</div>";
}

echo "</div>";

$page->finishRender();
?>

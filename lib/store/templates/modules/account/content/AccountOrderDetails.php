<?php
include_once("store/templates/modules/account/content/AccountContent.php");
include_once("store/components/order/OrderView.php");
include_once("components/Action.php");

/**
 * One order of the client (?orderID=) - only own orders
 */
class AccountOrderDetails extends AccountContent
{
    public function initialize() : void
    {
        SparkPage::Instance()->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/print.css");

        $view = new OrderView();
        if (!$view->setOrder((int)($_GET["orderID"] ?? -1), Module::Active()->getUserID())) {
            $this->redirect("orders", tr("No access to this order"));
        }

        $panel = new Container(false);
        $panel->setComponentClass("");

        $back = new Action("back", Module::PathURL("orders")->toString());
        $back->setClassName("ColorButton");
        $back->setText("<<");
        $panel->items()->append($back);

        $print = new Action("print", "javascript:window.print();");
        $print->setClassName("ColorButton");
        $print->setText(tr("Print"));
        $panel->items()->append($print);

        $panel->items()->append($view);

        $this->addColumn(AccountContent::Column(tr("Order details"), $panel, "details"));
    }

    protected function getContentTitle() : string
    {
        return tr("Order details");
    }
}

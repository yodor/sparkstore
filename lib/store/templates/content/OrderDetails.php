<?php
include_once("templates/TemplateContent.php");
include_once("store/components/order/OrderView.php");
include_once("store/responders/OrderConfirmationRequestResponder.php");

/**
 * Printable details of one order (?orderID=) - OrderView with the client's invoice details.
 * Page actions: print, send the order confirmation e-mail again.
 */
class OrderDetails extends TemplateContent
{
    protected int $orderID = -1;

    protected OrderView $view;

    protected OrderConfirmationRequestResponder $confirmation;

    public function __construct()
    {
        parent::__construct();
        $this->confirmation = new OrderConfirmationRequestResponder();
        $this->view = new OrderView();
        $this->view->setInvoiceDetailsEnabled(true);
    }

    public function setup(TemplateConfig $config): void
    {
        $this->orderID = (int)($_GET["orderID"] ?? -1);
        if (!$this->view->setOrder($this->orderID)) throw new Exception(tr("Order not found") . ": " . $this->orderID);

        $config->title = tr("Order Details") . ": " . $this->orderID;
        parent::setup($config);
    }

    public function fillPageActions(ActionCollection $collection): void
    {
        $print = new Action("print", "javascript:window.print();");
        $print->setContents(tr("Print"));
        $collection->append($print);

        $send = $this->confirmation->createAction(tr("Send confirmation e-mail"));
        $send->setURL(Module::PathURL("", $send->getURL()));
        $send->getURL()->add(new URLParameter("orderID", (string)$this->orderID));
        $collection->append($send);
    }

    public function initialize(): void
    {
        $this->cmp = $this->view;
    }

    protected function getContentTitle(): string
    {
        return tr("Order Details");
    }
}

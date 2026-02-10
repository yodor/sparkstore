<?php
include_once("templates/admin/BeanEditorPage.php");
include_once("store/utils/url/CategoryURL.php");
include_once("store/utils/url/ProductListURL.php");
include_once("store/utils/url/ProductURL.php");
include_once("store/forms/ProductInputFormBase.php");
include_once("store/beans/ProductsBean.php");
include_once("store/utils/CheckStockState.php");
include_once("objects/events/BeanFormEditorEvent.php");
include_once("objects/events/BeanTransactorEvent.php");



class ProductsListAdd extends BeanEditorPage
{

    public function __construct()
    {
        parent::__construct();
        $this->setForm(new ProductInputFormBase());
        $this->setBean(new ProductsBean());
    }

}

$template = new ProductsListAdd();

$closure = function(BeanFormEditorEvent $event) {
    if ($event->isEvent(BeanFormEditorEvent::EDITOR_CREATED)) {
        Debug::ErrorLog("Processing BeanFormEditorEvent::EDITOR_CREATED ...");
        $editor = $event->getSource();
        if (!($editor instanceof BeanFormEditor)) throw new Exception("Event source is not BeanFormEditor");

        $transactor = $editor->getTransactor();
        $transactor->assignInsertValue("insert_date", DBConnections::Open()->dateTime());
    }
};
SparkEventManager::register(BeanFormEditorEvent::class, new SparkObserver($closure));

$old_stock_amount = -1;
$closure_editor = function(BeanFormEditorEvent $event) use (&$old_stock_amount) {
    if ($event->isEvent(BeanFormEditorEvent::FORM_BEAN_LOADED)) {
        Debug::ErrorLog("Processing BeanFormEditorEvent::FORM_BEAN_LOADED ...");
        $editor = $event->getSource();
        if ($editor instanceof BeanFormEditor) {
            $old_stock_amount = $editor->getForm()->getInput("stock_amount")->getValue();
            Debug::ErrorLog("Current stock_amount: $old_stock_amount");
        }
    }
};
SparkEventManager::register(BeanFormEditorEvent::class, new SparkObserver($closure_editor));

$closure_transactor = function(BeanTransactorEvent $event) use(&$old_stock_amount) {
    if ($event->isEvent(BeanTransactorEvent::AFTER_COMMIT)) {
        Debug::ErrorLog("Processing BeanTransactorEvent::AFTER_COMMIT ...");
        $transactor = $event->getSource();
        if (!($transactor instanceof BeanTransactor)) return;
        $prodID = $transactor->getEditID();
        if ($prodID<1) return;

        $stock_amount = $transactor->getValue("stock_amount");
        $proc = new CheckStockState($prodID, $transactor->getValue("product_name"));
        $proc->process($stock_amount, $old_stock_amount);

        //remove cached version
        try {
            include_once("store/components/renderers/items/ProductDetailsItem.php");
            ProductDetailsItem::CleanCacheEntry($prodID);
        }
        catch (Exception $e) {
            Debug::ErrorLog("Exception during CleanCacheEntry: ".$e->getMessage());
        }
    }
};
SparkEventManager::register(BeanTransactorEvent::class, new SparkObserver($closure_transactor));


?>
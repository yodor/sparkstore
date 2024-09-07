<?php
include_once("templates/admin/BeanEditorPage.php");
include_once("store/utils/CheckStockState.php");

abstract class ProductsListAddBase extends BeanEditorPage
{
    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    abstract protected function init() : void;

    public function initView()
    {
        if (!$this->view) {
            parent::initView();

            $transactor = $this->getEditor()->getTransactor();
            $transactor->assignInsertValue("insert_date", DBConnections::Get()->dateTime());

            $old_stock_amount = -1;
            $closure_editor = function(BeanFormEditorEvent $event) use (&$old_stock_amount) {
                if ($event->isEvent(BeanFormEditorEvent::FORM_BEAN_LOADED)) {
                    debug("Closure handing form bean loaded");
                    $editor = $event->getSource();
                    if ($editor instanceof BeanFormEditor) {
                        $old_stock_amount = $editor->getForm()->getInput("stock_amount")->getValue();
                        debug("Current stock_amount: $old_stock_amount");
                    }
                }
            };
            SparkEventManager::register(BeanFormEditorEvent::class, new SparkObserver($closure_editor));

            $closure_transactor = function(BeanTransactorEvent $event) use(&$old_stock_amount) {
                if ($event->isEvent(BeanTransactorEvent::AFTER_COMMIT)) {
                    debug("Processing after commit");
                    $transactor = $event->getSource();
                    if (!($transactor instanceof BeanTransactor)) return;
                    $prodID = $transactor->getEditID();
                    if ($prodID<1) return;

                    $stock_amount = $transactor->getValue("stock_amount");
                    $proc = new CheckStockState($prodID, $transactor->getValue("product_name"));
                    $proc->process($stock_amount, $old_stock_amount);
                }
            };
            SparkEventManager::register(BeanTransactorEvent::class, new SparkObserver($closure_transactor));
        }
    }
}
?>

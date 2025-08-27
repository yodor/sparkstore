<?php
include_once("components/Container.php");
include_once("store/components/renderers/items/NavigationListItem.php");

abstract class NavigationList extends Container
{

    protected Container $list;
    protected NavigationListItem $item;

    protected SQLQuery $iterator;
    protected SQLSelect $tapeProducts;

    public Closure $createListIterator;
    public Closure $createTapeIterator;
    public Closure $createTapeProducts;

    public function __construct()
    {
        parent::__construct(false);

        $this->setTagName("nav");
        $this->setComponentClass("category_list");
        $this->setAttribute("itemscope");
        $this->setAttribute("itemtype", "https://schema.org/SiteNavigationElement");

        $this->setAttribute("role", "navigation");

        $this->list = new Container(false);
        $this->list->setTagName("UL");
        $this->list->setComponentClass("");

        $this->list->items()->append(new ClosureComponent($this->renderItems(...), false, false));

        $this->items()->append($this->list);

        $this->item = new NavigationListItem();

        $this->createListIterator = function() {
            return $this->createListIterator();
        };

        $this->createTapeIterator = function() {
            return $this->createTapeIterator();
        };

        $this->createTapeProducts = function() {
            return $this->createTapeProducts();
        };
    }

    public function initialize() : void
    {
        $this->iterator = ($this->createListIterator)();
        $this->tapeProducts = ($this->createTapeProducts)();
    }

    abstract protected function createListIterator() : SQLQuery;

    abstract protected function createTapeIterator() : ?SQLQuery;

    protected function createTapeProducts() : SQLSelect
    {
        $sellable = new SellableProducts();
        $select = $sellable->query(...$sellable->columnNames())->select;

        $select->fields()->unset($this->item->getValueKey());
        $select->fields()->unset($this->item->getLabelKey());

        $select->order_by = " RAND() ";

        $select->where()->add("stock_amount", 0, ">");

        $select->limit = " 4 ";
        return $select;
    }

    public function getItem() : NavigationListItem
    {
        return $this->item;
    }

    public function getIterator() : SQLQuery
    {
        return $this->iterator;
    }

    protected function renderItems() : void
    {
        $this->iterator->exec();
        $position = 0;
        while ($result = $this->iterator->next())
        {
            $this->item->setPosition($position);
            $this->item->setData($result);

            $this->item->setRenderEnabled(true);

            $query = ($this->createTapeIterator)();
            if ($query instanceof SQLQuery) {
                //tape iterator is set enable tape rendering
                $this->item->getTape()->setRenderEnabled(true);

                $num = $query->exec();
                if ($num > 0) {
                    $this->item->getTape()->setIterator($query);
                }
                else {
                    //do not render item
                    $this->item->setRenderEnabled(false);
                }
            }
            else {
                //tape iterator is null disable tape rendering
                $this->item->getTape()->setRenderEnabled(false);
            }

            $this->item->render();

            $position++;
        }
    }
}
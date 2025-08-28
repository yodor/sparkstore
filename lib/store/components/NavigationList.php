<?php
include_once("components/Container.php");
include_once("components/ClosureComponent.php");
include_once("sql/SQLSelect.php");
include_once("iterators/SQLQuery.php");

include_once("store/components/renderers/items/NavigationListItem.php");
include_once("store/beans/SellableProducts.php");

abstract class NavigationList extends Container
{

    protected Container $list;
    protected NavigationListItem $item;

    protected SQLQuery $iterator;
    protected SQLSelect $tapeProducts;

    public Closure $createListIterator;
    public Closure $createTapeIterator;
    public Closure $createTapeProducts;

    public Closure $createImagesColumn;

    public int $imagesLimit = 4;

    public function __construct()
    {
        parent::__construct(false);

        $this->setTagName("nav");
        $this->setComponentClass("category_list");
        $this->setAttribute("itemscope");
        $this->setAttribute("itemtype", "https://schema.org/SiteNavigationElement");

        $this->setAttribute("role", "navigation");

        $this->list = new ClosureComponent($this->renderItems(...), true, false);
        $this->list->setTagName("UL");
        $this->list->setComponentClass("");

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

        $this->createImagesColumn = function(SQLSelect $select) {
            $this->createImagesColumn($select);
        };
    }

    public function initialize() : void
    {
        $this->iterator = ($this->createListIterator)();
        ($this->createImagesColumn)($this->iterator->select);

        $this->tapeProducts = ($this->createTapeProducts)();
    }

    abstract public function createListIterator() : SQLQuery;

    abstract public function createTapeIterator() : ?SQLQuery;

    abstract public function createImagesColumn(SQLSelect $select) : void;

    public function createTapeProducts() : SQLSelect
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

?>
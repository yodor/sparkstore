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

    protected IDataIterator $iterator;
    protected SQLSelect $tapeProducts;

    public Closure $createListIterator;
    public Closure $createTapeIterator;
    public Closure $createTapeProducts;

    public Closure $createImagesColumn;

    public int $imagesLimit = 4;

    //do not render banner or text if tape iterator is empty
    public bool $emptyTapeDisableItem = true;

    public function __construct()
    {
        parent::__construct(false);

        $this->setTagName("nav");
        $this->setComponentClass("category_list");
        $this->setAttribute("itemscope");
        $this->setAttribute("itemtype", "https://schema.org/SiteNavigationElement");

        $this->setAttribute("role", "navigation");

        $this->list = new ClosureComponent($this->renderItems(...), true, false);
        $this->list->setTagName("ul");
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
        $this->setCacheable(true);
    }

    public function getCacheName() : string
    {
        $result = parent::getCacheName();
        if ($this->iterator instanceof SQLQuery)
        {
            $result.= $this->iterator->select->getSQL();
        }
        return $result;
    }
    public function initialize() : void
    {
        $this->iterator = ($this->createListIterator)();
        ($this->createImagesColumn)($this->iterator->select);

        $this->tapeProducts = ($this->createTapeProducts)();
    }

    public function setCacheable(bool $mode): void
    {
        parent::setCacheable($mode);
        $this->item->setCacheable($mode);
    }

    /**
     * Return SQLQuery for all items in this list
     * @return SQLQuery|null
     */
    abstract public function createListIterator() : ?SQLQuery;

    /**
     * Return SQLQuery with all the products to be shown in 'this' item of the list,
     * implementors modify the select returned from createTapeProducts
     * @return SQLQuery|null
     */
    abstract public function createTapeIterator() : ?SQLQuery;

    abstract public function createImagesColumn(SQLSelect $select) : void;

    /**
     * Return SQLSelect with all the products that will be used for display in this NavigationList
     * @return SQLSelect
     */
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

    public function getIterator() : IDataIterator
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
                    if ($this->emptyTapeDisableItem) {
                        $this->item->setRenderEnabled(false);
                    }
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
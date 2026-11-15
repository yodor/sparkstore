<?php
include_once("components/Container.php");
include_once("components/ClosureComponent.php");
include_once("sql/SQLSelect.php");
include_once("iterators/SelectQuery.php");

include_once("store/components/renderers/items/NavigationListItem.php");
include_once("store/beans/SellableProducts.php");

abstract class NavigationList extends Container
{

    protected ?Container $list = null;
    protected ?NavigationListItem $item = null;

    protected ?IDataIterator $iterator = null;
    protected ?SQLSelect $tapeProducts = null;

    /**
     * Main iterator - each element of the NavigationList - ie main categories to list or sections
     * @var Closure
     */
    public Closure $createListIterator;

    /**
     * Iterator for the items listed inside each NavigationList element
     * This can be disabled so only banners are shown and no related items are listed
     * @var Closure
     */
    public Closure $createTapeIterator;

    /**
     *
     * @var Closure
     */
    public Closure $createTapeProducts;

    /**
     * @var Closure
     */
    public Closure $createImagesColumn;

    public int $imagesLimit = 4;

    public int  $tapeItemsLimit = 4;
    public bool $tapeItemsRandom = true;

    //do not render items with empty/disabled tape and no banners
    //If this is false captions will be rendered only - even if not tape items or images
    public bool $disableEmptyItems = true;

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
        if ($this->iterator instanceof ICacheIdentifier) $result.= $this->iterator->getCacheName();

        return $result;
    }
    public function initialize() : void
    {
        $this->iterator = ($this->createListIterator)();
        ($this->createImagesColumn)($this->iterator->stmt);

        $this->tapeProducts = ($this->createTapeProducts)();
    }

    /**
     * Return SelectQuery for all items in this list
     * @return SelectQuery|null
     */
    abstract public function createListIterator() : ?SelectQuery;

    /**
     * Return SelectQuery with all the products to be shown in 'this' item of the list,
     * implementors modify the select returned from createTapeProducts
     * @return SelectQuery|null
     */
    abstract public function createTapeIterator() : ?SelectQuery;

    abstract public function createImagesColumn(SQLSelect $select) : void;

    /**
     * Return SQLSelect with all the products that will be used for display in this NavigationList
     *
     * @return SQLSelect
     * @throws Exception
     */
    public function createTapeProducts() : SQLSelect
    {
        $sellable = new SellableProducts();

        $select = SQLSelect::Table($sellable->table());

        //TODO: set required columns for ProductListItem only
        //Get required columns from ProductListItem instance
        //$columns = ["prodID", "product_name", "sell_price", "price", "stock_amount", "category_name", "class_name", "ppID", "discount_percent"];
        $columns = ProductsTape::GetDefaultItemRenderer()->collectDataKeys();
        $present = $sellable->ensureColumns($columns);

        $select->columns(...$present);

        $select->columns()->setPrefix("sellable_products");

        //do not rand here
        //$select->order_by = " RAND() ";

        $select->where()->expression("stock_amount > 0");

        $select->limit($this->tapeItemsLimit);

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

    /**
     * Up to tapeItemsLimit keys of the tape query - random ones if tapeItemsRandom
     * @param SelectQuery $query
     * @return array<int>
     */
    protected function randomKeys(SelectQuery $query) : array
    {
        $select = clone $query->stmt;
        $select->columns()->reset();
        $select->column($this->qualifiedKey($query));
        $select->orderClear();
        if ($this->tapeItemsRandom) $select->order("RAND()");
        $select->limit($this->tapeItemsLimit);

        $keys = array();
        $lookup = new SelectQuery($select, $query->key());
        $lookup->exec();
        while ($row = $lookup->next()) {
            $keys[] = (int)$row[$query->key()];
        }
        $lookup->free();
        return $keys;
    }

    /**
     * The key column of the query as selected (with its table or alias prefix) - unambiguous in joins
     * @param SelectQuery $query
     * @return string
     */
    protected function qualifiedKey(SelectQuery $query) : string
    {
        foreach ($query->stmt->columns()->names() as $name) {
            if ($name === $query->key() || str_ends_with($name, "." . $query->key())) return $name;
        }
        return $query->table() ? $query->table() . "." . $query->key() : $query->key();
    }

    protected function renderItems() : void
    {
        //element like sections, categories other collections that might have images/banners and/or related products/items to show
        $this->iterator->exec();

        $position = 0;
        while ($result = $this->iterator->next())
        {
            $this->item->setPosition($position);
            //process caption and images/banners if any
            $this->item->setData($result);

            //update any previous rendering state
            $this->item->setRenderEnabled(true);

            //for each element of the NavigationList
            $query = ($this->createTapeIterator)();

            //total items for ProductsTape to render
            $total = -1;

            //we want tape items - one query picks up to tapeItemsLimit random keys, the tape then reads
            //those rows by primary key (replaces COUNT + random OFFSET: two scans per item)
            if ($query instanceof SelectQuery) {
                $keys = $this->randomKeys($query);
                $total = count($keys);
                if ($total > 0) {
                    //same query restricted to the picked keys - reads only those rows
                    $query->stmt->limitClear();
                    $query->stmt->where()->expression($this->qualifiedKey($query) . " IN (" . implode(",", $keys) . ")");
                    $query->execLateLookup = false;

                    $this->item->getTape()->setIterator($query);
                    //tape iterator is set enable tape rendering
                    $this->item->getTape()->setRenderEnabled(true);
                }
            }

            //tape iterator is empty or no iterator at all created
            if ($total < 1) {
                //NavigationListItem might have images/banners - disable only tape items
                $this->item->getTape()->setRenderEnabled(false);
            }

            //no rendering for this NavigationListItem skip position update too
            if ($total<1 && $this->item->bannersCount()<1 && $this->disableEmptyItems) continue;


            //render the NavigationListItem
            $this->item->render();
            $position++;
        }
    }
}
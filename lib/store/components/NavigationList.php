<?php
include_once("components/Container.php");
include_once("components/ClosureComponent.php");
include_once("sql/SQLSelect.php");
include_once("iterators/SelectQuery.php");

include_once("store/components/renderers/items/NavigationListItem.php");
include_once("store/beans/SellableProducts.php");
include_once("store/responders/json/NavigationTapeResponder.php");

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

    /**
     * Items rendered with their product tape in the page - the tapes of the following items are loaded when
     * they scroll into view (NavigationTapeResponder, storefront/js/NavigationTapes.js). -1: all tapes in the page.
     * @var int
     */
    public int $eagerItems = -1;

    /**
     * Index of this list in the page responder
     * @var int
     */
    protected int $listIndex = -1;

    /**
     * One responder for all navigation lists of the page
     * @var NavigationTapeResponder|null
     */
    protected static ?NavigationTapeResponder $tapeResponder = null;

    /**
     * Wraps one tape in the responder output - <div class='TapeResult' item='<value>'>
     * @var Container|null
     */
    protected ?Container $tapeResult = null;

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

        if (is_null(NavigationList::$tapeResponder)) {
            NavigationList::$tapeResponder = new NavigationTapeResponder();
        }
        $this->listIndex = NavigationList::$tapeResponder->addList($this);
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

    /**
     * Set up the tape of the current item: up to tapeItemsLimit (random) products, read by key
     * @return int number of tape products - 0 disables the tape
     */
    protected function prepareTape() : int
    {
        $total = 0;
        $query = ($this->createTapeIterator)();
        //one query picks the keys, the tape then reads those rows (replaces COUNT + random OFFSET)
        if ($query instanceof SelectQuery) {
            $keys = $this->randomKeys($query);
            $total = count($keys);
            if ($total > 0) {
                //same query restricted to the picked keys - reads only those rows
                $query->stmt->limitClear();
                $query->stmt->where()->expression($this->qualifiedKey($query) . " IN (" . implode(",", $keys) . ")");
                $query->execLateLookup = false;
                $this->item->getTape()->setIterator($query);
            }
        }
        $this->item->getTape()->setRenderEnabled($total > 0);
        return $total;
    }

    /**
     * Render the tapes of the items with value in '$values' (NavigationTapeResponder) - each wrapped in
     * <div class='TapeResult' item='<value>'>, empty for an item without products. Unknown values are ignored.
     * @param array<string> $values
     * @return void
     */
    public function renderTapes(array $values) : void
    {
        if (is_null($this->tapeResult)) {
            $this->tapeResult = new Container(false);
            $this->tapeResult->setComponentClass("TapeResult");
        }

        $this->iterator->exec();
        while ($result = $this->iterator->next()) {
            $this->item->setData($result);
            $value = (string)$this->item->getValue();
            if (!in_array($value, $values, true)) continue;

            $this->prepareTape();
            $this->tapeResult->setAttribute("item", $value);
            $this->tapeResult->items()->clear();
            $this->tapeResult->items()->append($this->item->getTape());
            $this->tapeResult->render();
        }
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

            //tapes after the first eagerItems are loaded when they scroll into view
            if ($this->eagerItems >= 0 && $position >= $this->eagerItems) {
                $this->item->getTape()->setRenderEnabled(false);
                if (($this->createTapeIterator)() instanceof SelectQuery) {
                    $this->item->setLazyTape(NavigationList::$tapeResponder->getName(), $this->listIndex);
                }
                else {
                    $this->item->clearLazyTape();
                    if ($this->item->bannersCount() < 1 && $this->disableEmptyItems) continue;
                }
            }
            else {
                $this->item->clearLazyTape();
                $total = $this->prepareTape();
                //no rendering for this NavigationListItem skip position update too
                if ($total < 1 && $this->item->bannersCount() < 1 && $this->disableEmptyItems) continue;
            }

            //render the NavigationListItem
            $this->item->render();
            $position++;
        }
    }
}
<?php
include_once("components/Container.php");
include_once("components/renderers/items/DataIteratorItem.php");

class NavigationListItem extends DataIteratorItem
{
    protected Action $action;
    protected Component $span;
    protected Image $image;
    protected StorageItem $si;

    protected ProductsTape $tape;

    public function __construct()
    {
        parent::__construct();
        $this->setTagName("LI");
        $this->setComponentClass("");


        $this->action = new Action();
        $this->action->setComponentClass("item");
        $this->action->setAttribute("itemprop", "url");
        $this->items()->append($this->action);

        $this->span = new Component();
        $this->span->setTagName("SPAN");
        $this->span->setComponentClass("Caption");
        $this->span->setAttribute("itemprop", "name");
        $this->action->items()->append($this->span);

        $this->si = new StorageItem();
        $this->image = new Image();
        $this->image->setStorageItem($this->si);

        $this->action->items()->append($this->image);

        $this->tape = new ProductsTape();
        $this->tape->getCaptionComponent()->setRenderEnabled(false);
        $this->items()->append($this->tape);
        $this->tape->setRenderEnabled(false);

    }

    public function setData(array $data) : void
    {
        parent::setData($data);
        $this->action->setTitle($this->label);
        $this->action->getURL()->setData($data);

        $this->span->setContents($this->label);

        $this->image->setTitle($this->label);
        $this->si->id = -1;

        $this->image->setRenderEnabled(false);
        $this->si->setData($data);
        if ($this->si->id > 0) {
            $this->image->setRenderEnabled(true);
        }

        $this->tape->setClassName("item {$this->label}");
    }

    public function getTape() : ProductsTape
    {
        return $this->tape;
    }

    public function getAction() : Action
    {
        return $this->action;
    }

    public function getStorageItem() : StorageItem
    {
        return $this->si;
    }

    public function getImage() : Image
    {
        return $this->image;
    }
}

class CategoryNavigation extends Container
{

    protected ProductsTape $tape;
    protected SQLSelect $productsList;
    protected SQLQuery $iterator;

    protected Container $list;
    protected NavigationListItem $item;

    public function __construct()
    {
        parent::__construct(false);

        $this->setTagName("nav");
        $this->setComponentClass("category_list");
        $this->setAttribute("itemscope");
        $this->setAttribute("itemtype", "https://schema.org/SiteNavigationElement");
        $this->setAttribute("aria-label", "Product Categories");
        $this->setAttribute("role", "navigation");

        $this->tape = new ProductsTape();
        $this->tape->setCacheable(true);

        $this->list = new Container(false);
        $this->list->setTagName("UL");
        $this->list->setComponentClass("");

        $this->list->items()->append(new ClosureComponent($this->renderItems(...), false, false));

        $this->items()->append($this->list);

        $this->item = new NavigationListItem();

        $this->setIterator($this->createCategoriesIterator());

    }

    public function setIterator(SQLQuery $iterator): void
    {
        $this->iterator = $iterator;
    }

    public function createCategoriesIterator() : SQLQuery
    {
        $select = new SQLSelect();
        $select->fields()->set("pc.catID, pc.category_name");
        $select->fields()->setExpression("(SELECT pcpID FROM product_category_photos pcp WHERE pcp.catID = pc.catID ORDER BY position ASC LIMIT 1)", "pcpID");
        $select->from = " product_categories pc ";
        $select->where()->add("pc.parentID", 0);
        $select->order_by = " pc.lft ";

        $this->item->setValueKey("catID");
        $this->item->setLabelKey("category_name");
        $this->item->getStorageItem()->className="ProductCategoryPhotosBean";
        $this->item->getStorageItem()->setValueKey("pcpID");
        $this->item->getAction()->setURL(new CategoryURL());
        return new SQLQuery($select, "catID");
    }

    public function createCategoryTapeIterator() : SQLSelect
    {
        $sellable = new SellableProductsList();
        $select = $sellable->query(...$sellable->columnNames())->select;

        $select->fields()->unset("catID");
        $select->fields()->unset("category_name");
        $select->order_by = " RAND() ";
//$query->select->group_by = " prodID ";
        $select->where()->add("stock_amount", 0, ">");

        $select->limit = " 4 ";
        return $select;
    }

    public function getListItem() : NavigationListItem
    {
        return $this->item;
    }

    protected function renderItems() : void
    {
        $this->iterator->exec();
        $position = 0;
        while ($result = $this->iterator->next())
        {
            $this->item->setPosition($position);
            $this->item->setData($result);
            $this->item->render();
            $position++;
        }
    }
//    protected function renderItems() : void
//    {
//        $sectionURL = new CategoryURL();
//        $this->tape->getAction()->setURL($sectionURL);
//
//        while ($category = $this->iterator->next()) {
//
//            $sectionName = $category["category_name"];
//            $catID = $category["catID"];
//
//            //match all products whose catID is nodeID or nodeID child nodes
//            $tape_select = $this->iterator->bean()->selectChildNodesWith($this->productsList, "sellable_products_list", $catID, array("catID", "category_name"));
//            //echo $tape_select->getSQL();
//            $this->tape->setClassName("item $sectionName");
//
//            $sectionURL->setData($category);
//
//            $this->tape->setCaption($sectionName);
//
//            $this->tape->setIterator(new SQLQuery($tape_select, "prodID"));
//            $this->tape->render();
//        }
//
//
//    }

}
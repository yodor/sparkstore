<?php
include_once("store/pages/ProductPageBase.php");
include_once("store/components/ProductsTape.php");

include_once("store/utils/PriceInfo.php");
include_once("store/utils/SellableItem.php");
include_once("store/components/renderers/items/ProductDetailsItem.php");

class ProductDetailsPageBase extends ProductPageBase
{

    protected SellableItem $sellable;

    protected ?ProductDetailsItem $item = null;

    public function __construct()
    {
        //should reach storepage constructor to initialize defaults
        //is sellabledataparaser is customizable
        parent::__construct();

        $this->head()->addCSS(STORE_LOCAL . "/css/product_details.css");
        $this->head()->addCSS(STORE_LOCAL . "/css/ProductListItem.css");

        $prodID = -1;
        if (isset($_GET["prodID"])) {
            $prodID = (int)$_GET["prodID"];
        }

        try {
            $this->bean = new SellableProducts();
            //needs the default parser set correctly
            $this->sellable = SellableItem::Load($prodID);
        }
        catch (Exception $e) {
            Session::set("alert", "Този продукт е недостъпен. Грешка: " . $e->getMessage());
            header("Location: list.php");
            exit;
        }

        $this->section = "";

        $catID = $this->sellable->getCategoryID();
        $this->loadCategoryPath($catID);

        $this->prepareKeywords();
        $this->prepareDescription();

        $main_photo = $this->sellable->getMainPhoto();
        if ($main_photo instanceof StorageItem) {
            $this->head()->addOGTag("image", fullURL($this->sellable->getMainPhoto()->hrefImage(600, -1)));

            $this->head()->addOGTag("image:height", "600");
            $this->head()->addOGTag("image:width", "600");
            $this->head()->addOGTag("image:alt", $this->sellable->getTitle());
        }

        register_shutdown_function(function()  {
            ProductDetailsPageBase::UpdateViewCounter($this->sellable->getProductID());
        });


        $this->head()->addCanonicalParameter("prodID");

        $this->item = $this->createDetailsItem();
    }

    protected function createDetailsItem() : ProductDetailsItem
    {
        return new ProductDetailsItem($this->getSellable());
    }

    public function getDetailsItem() : ProductDetailsItem
    {
        return $this->item;
    }

    public function initialize() : void
    {
        $this->item->setURL(URL::Current()->fullURL());
        $this->item->setCategories($this->getCategoryPath());
        $this->item->initialize();
    }

    protected function renderImpl() : void
    {
        $this->renderCategoryPath();

        $this->item->render();

        $this->renderProductTapes();
    }

    protected function renderProductTapes() : void
    {
        $cmp = $this->createTapeContainer("same_category");
        $cmp->items()->append($this->tapeSameCategory());
        $cmp->render();

//        $cmp = $this->createTapeContainer("other_products");
//        $cmp->items()->append($this->tapeOtherProducts());
//        $cmp->render();
    }
    /**
     * Use the product name as page title tag
     * @return void
     */
    protected function constructTitle() : void
    {
        $this->setTitle($this->sellable->getTitle());
    }

    /**
     * @return string
     */
    protected function prepareKeywords() : string
    {
        $catID = $this->sellable->getCategoryID();

        //use keywords of the sellable if set
        $keywords = sanitizeKeywords($this->sellable->getKeywords());
        if (mb_strlen($keywords)>0) {
            $this->keywords = $keywords;
            return $keywords;
        }

        //no keywords added for this sellable. try category keywords if any
        $this->loadCategoryPath($catID);
        $keywords = parent::prepareKeywords();
        //no category keywords were set, use the sellable title as keywords
        if (mb_strlen($keywords)<1) {
            $keywords = $this->sellable->getTitle();
        }

        $this->keywords = $keywords;
        return $keywords;
    }

    protected function prepareDescription(): string
    {
        $description = "";

        if ($this->sellable->getDescription()) {
            $description = $this->sellable->getDescription();
        }
        else {
            $description = $this->sellable->getTitle();
        }

        $description = trim($description);
        if ($description) {
            $description = prepareMeta($description);
            $this->description = $description;
        }

        return $description;
    }

    public function getSellable(): SellableItem
    {
        return $this->sellable;
    }



    protected function selectActiveMenu()
    {
        parent::selectActiveMenu();

        $main_menu = $this->menu_bar->getMenu();

        $iterator = $main_menu->iterator();
        while ($item = $iterator->next()) {
            if (!($item instanceof MenuItem))continue;
            if (strcmp($item->getName(), $this->section) == 0) {
                $main_menu->deselect();
                $item->setSelected(true);
                break;
            }
        }

    }

    public function tapeSameCategory(int $limit = 4) : ProductsTape
    {
        $catID = (int)$this->sellable->getCategoryID();

        $title = tr("Още продукти от тази категория");

        $select = clone $this->bean->select();
        $select->fields()->setPrefix("sellable_products");
        $select = $this->product_categories->selectChildNodesWith($select, "sellable_products", $catID, array());

//        echo $select->getSQL();
        $qry = new SQLQuery($select, "prodID");

//        $qry = $this->bean->queryFull();
//        $qry->select->where()->add("catID", $catID);
        $qry->select->where()->add("stock_amount" , "0", " > ");
        $qry->select->order_by = " rand() ";
        $qry->select->group_by = " prodID ";
        $qry->select->limit = "$limit";

        $tape = new ProductsTape();
        $tape->getListItem()->setProductLinkedDataEnabled(false);
        $tape->setCaption($title);
        $tape->setIterator($qry);

        $tape->getCaptionURL()->fromString(LOCAL . "/products/list.php");
        $tape->getCaptionURL()->add(new URLParameter("catID", $catID));

        return $tape;
    }

    public function tapeOtherProducts(int $limit = 4) : ProductsTape
    {
        $title = tr("Други продукти");

        $qry = $this->bean->queryFull();
        $qry->select->order_by = " rand() ";
        $qry->select->group_by = " prodID ";
        $qry->select->where()->add("stock_amount" , "0", " > ");
        $qry->select->limit = "$limit";

        $tape = new ProductsTape();
        $tape->getListItem()->setProductLinkedDataEnabled(false);
        $tape->setCaption($title);
        $tape->setIterator($qry);
        $tape->getCaptionURL()->fromString(LOCAL."/products/list.php");

        return $tape;
    }

    public function createTapeContainer(string $name) : Container
    {
        $cmp = new Container(false);
        $cmp->setComponentClass("product_group");
        $cmp->setClassName($name);
        return $cmp;
    }

    protected static function UpdateViewCounter(int $prodID) : void
    {
        debug("Updating view counter for prodID: " . $prodID);

        //INSERT INTO product_view_log (prodID, view_counter, order_counter) select p.prodID, coalesce(p.view_counter,0), coalesce(p.order_counter,0) FROM products p ON DUPLICATE KEY UPDATE view_counter=coalesce(p.view_counter,0), order_counter=coalesce(p.order_counter,0)
//        $db = DBConnections::Open();
//        try {
//            $db->transaction();
//            $db->query("INSERT INTO product_view_log (prodID, view_counter, order_counter) VALUES ($prodID, 1, 0) ON DUPLICATE KEY UPDATE view_counter=(view_counter+1)");
//            $db->commit();
//        }
//        catch (Exception $e) {
//            $db->rollback();
//            debug("Unable to increment view counter: ".$e->getMessage());
//        }

    }
}

?>

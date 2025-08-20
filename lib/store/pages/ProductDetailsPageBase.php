<?php
include_once("store/pages/ProductPageBase.php");
include_once("store/components/ProductsTape.php");

include_once("store/utils/PriceInfo.php");
include_once("store/utils/SellableItem.php");
include_once("store/components/renderers/items/ProductDetailsItem.php");

class ProductDetailsPageBase extends ProductPageBase
{

    protected ?SellableItem $sellable = null;

    protected ?ProductDetailsItem $item = null;

    public function __construct()
    {
        //should reach StorePage constructor to initialize defaults
        //SellableDataParser is customizable
        parent::__construct();

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
            StorePage::ErrorPage("Този продукт е недостъпен. Грешка: " . $e->getMessage(),404);
        }


        $this->section = "";

        $catID = $this->sellable->getCategoryID();
        $this->loadCategoryPath($catID);

        register_shutdown_function(function()  {
            ProductDetailsPageBase::UpdateViewCounter($this->sellable->getProductID());
        });


        $this->head()->addCSS(STORE_LOCAL . "/css/product_details.css");
        $this->head()->addCSS(STORE_LOCAL . "/css/ProductListItem.css");

        $this->head()->addCanonicalParameter("prodID");
        $this->head()->addCanonicalParameter("product_name");

        $this->item = $this->createDetailsItem();

        //$this->head()->addOGTag("url", fullURL($this->currentURL()));

    }

    protected function headFinalize(): void
    {
        $this->preferred_title = $this->sellable->getTitle();

        $description = $this->sellable->getTitle();

        if ($this->sellable->getSeoDescription()) {
            $description = $this->sellable->getSeoDescription();
        }
        else if ($this->sellable->getDescription()) {
            $description = $this->sellable->getDescription();
        }

        $description = trim($description);
        if (mb_strlen($description)>0) {
            $this->description = $description;
        }

        //fill additional SEO data
        $this->head()->addOGTag("type", "product");
        $this->head()->addOGTag("title", $this->sellable->getTitle());

        $this->head()->addOGTag("product:price:amount", $this->sellable->getPriceInfo()->getSellPrice());
        $this->head()->addOGTag("product:price:currency", "BGN");

        $main_photo = $this->sellable->getMainPhoto();
        if ($main_photo instanceof StorageItem) {
            $main_photo->setName($this->sellable->getTitle());

            $this->head()->addOGTag("image", fullURL($main_photo->hrefImage(600, 0)));

            $this->head()->addOGTag("image:height", "600");
            $this->head()->addOGTag("image:width", "600");

            $this->head()->addOGTag("image:alt", $this->sellable->getTitle());
        }

        $this->head()->addMeta("twitter:card", "summary_large_image");
        $this->head()->addMeta("twitter:image", fullURL($main_photo->hrefImage(600, 0)));

        //no parent call
        //parent::headFinalize();
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
        $this->item->setCategories($this->getCategoryPath());
        $this->item->initialize();
    }

    protected function renderImpl() : void
    {
        $this->renderCategoryPath();

        $this->item->render();

        $this->renderProductTapes();
    }

    protected function constructPathActions(): array
    {
        $actions = parent::constructPathActions();

        $action = new Action($this->sellable->getTitle(), $this->currentURL(), array());
        $action->translation_enabled = false;

        $actions[] = $action;
        return $actions;
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

    public function getSellable(): SellableItem
    {
        return $this->sellable;
    }

    protected function selectActiveMenu(): void
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

        $categoryURL = new CategoryURL();
        $categoryURL->setCategoryID($catID);
        $categoryURL->setCategoryName($this->sellable->getCategoryName());

        $tape->getAction()->setURL($categoryURL);

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

        $tape->getAction()->setURL(new ProductListURL());

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
        $db = DBConnections::Open();
        try {
            $db->transaction();
            $db->query("INSERT INTO product_view_log (prodID, view_counter, order_counter) VALUES ($prodID, 1, 0) ON DUPLICATE KEY UPDATE view_counter=(view_counter+1)");
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollback();
            debug("Unable to increment view counter: ".$e->getMessage());
        }

    }

    //return slugified url if category is selected
    public function currentURL() : URL
    {
        return clone $this->item->getURL();
    }
}

?>

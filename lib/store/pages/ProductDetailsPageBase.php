<?php
include_once("store/pages/ProductPageBase.php");
include_once("store/components/ProductsTape.php");

include_once("store/utils/PriceInfo.php");
include_once("store/utils/SellableItem.php");
include_once("store/components/renderers/items/ProductDetailsItem.php");
include_once("store/components/TapeSameCategory.php");

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


        $this->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/product_details.css");
        $this->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/ProductListItem.css");

        $this->head()->addCanonicalParameter("prodID");
        $this->head()->addCanonicalParameter("product_name");

        $this->item = $this->createDetailsItem();

        $this->_main->content()->setTagName("main");
        $this->_main->content()->setRole("main");
    }

    /**
     * Inner page layout initialization
     * @return void
     */
    private function initPrivate(): void
    {
        $this->items()->append($this->breadcrumb);
        $this->items()->append($this->item);
        $this->initProductTapes();
    }

    protected function applyTitleDescription(): void
    {
        parent::applyTitleDescription();

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

    }

    protected function headFinalize(): void
    {
        parent::headFinalize();

        //fill additional SEO data
        $this->head()->addOGTag("type", "product");

        $this->head()->addOGTag("product:price:amount", $this->sellable->getPriceInfo()->getSellPrice());
        $this->head()->addOGTag("product:price:currency", Spark::Get(StoreConfig::DEFAULT_CURRENCY));

        $main_photo = $this->sellable->getMainPhoto();
        if ($main_photo instanceof StorageItem) {
            $main_photo->setName($this->sellable->getTitle());

            $width = $this->item->getGallery()->getPhotoWidth();
            $height = $this->item->getGallery()->getPhotoHeight();
            $imageURL = $main_photo->hrefImage($width, $height)->fullURL();
            $this->head()->addOGTag("image", $imageURL);

            $this->head()->addOGTag("image:height", $width);
            $this->head()->addOGTag("image:width", $height);

            $this->head()->addOGTag("image:alt", $this->sellable->getTitle());

            $this->head()->addMeta("twitter:card", "summary_large_image");
            $this->head()->addMeta("twitter:image", $imageURL);
        }



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

        $this->initPrivate();

        $this->item->setCategories($this->getCategoryPath());
        $this->item->initialize();
        $this->fillBreadCrumb();
    }

    protected function constructPathActions(): array
    {
        $actions = parent::constructPathActions();

        $action = new Action($this->sellable->getTitle(), $this->currentURL(), array());
        $actions[] = $action;
        return $actions;
    }

    protected function initProductTapes() : void
    {
        $cmp = new TapeSameCategory($this->sellable);
        $this->items()->append($cmp);

    }

    public function getSellable(): SellableItem
    {
        return $this->sellable;
    }

    protected static function UpdateViewCounter(int $prodID) : void
    {
        Debug::ErrorLog("Updating view counter for prodID: " . $prodID);

        //INSERT INTO product_view_log (prodID, view_counter, order_counter) select p.prodID, coalesce(p.view_counter,0), coalesce(p.order_counter,0) FROM products p ON DUPLICATE KEY UPDATE view_counter=coalesce(p.view_counter,0), order_counter=coalesce(p.order_counter,0)
        $db = DBConnections::Open();
        try {
            $db->transaction();
            $db->query("INSERT INTO product_view_log (prodID, view_counter, order_counter) VALUES ($prodID, 1, 0) ON DUPLICATE KEY UPDATE view_counter=(view_counter+1)");
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollback();
            Debug::ErrorLog("Unable to increment view counter: ".$e->getMessage());
        }

    }

    //return slugified url if category is selected
    public function currentURL() : URL
    {
        return clone $this->item->getURL();
    }
}

?>

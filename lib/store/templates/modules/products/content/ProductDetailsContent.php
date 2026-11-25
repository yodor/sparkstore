<?php
include_once("utils/script/Marketing.php");
include_once("store/templates/modules/products/content/ProductContent.php");
include_once("store/beans/SellableProducts.php");
include_once("store/beans/ProductViewLogBean.php");
include_once("store/utils/SellableItem.php");
include_once("store/components/renderers/items/ProductDetailsItem.php");
include_once("store/components/TapeSameCategory.php");
include_once("store/objects/data/GTMViewItemEvent.php");
include_once("components/TextComponent.php");

/**
 * A product (products module path 'details' - /products/<prodID>/<slug>/): the breadcrumb, the details item, the
 * product tapes. One address per product (301 to the current slug), the title and description of the product,
 * the og / twitter product tags, the GTM view item event, the view counter.
 *
 * A site subclasses it in its products content folder and names it in its path file details.php:
 *  - createDetailsItem() - its own details item (BookshopDetailsItem)
 *  - productTitle() - its own title rule
 *  - initProductTapes() - its own tapes
 * An unknown product answers 404.
 */
class ProductDetailsContent extends ProductContent
{
    protected ?SellableProducts $bean = null;

    protected ?SellableItem $sellable = null;

    protected ?ProductDetailsItem $item = null;

    public function initialize() : void
    {
        $page = SparkPage::Instance();
        $this->cmp = new Container(false);

        //the body class of the former page class - the site css (.ProductDetailsPage ...)
        $page->body()->addClassName("ProductDetailsPage");

        new ProductViewLogBean();
        $this->bean = new SellableProducts();

        try {
            //needs the default parser set correctly (the site StorePage)
            $this->sellable = SellableItem::Load((int)($_GET["prodID"] ?? -1));
        }
        catch (Exception $e) {
            Debug::ErrorLog("Product not available: " . $e->getMessage());
            $this->notFound($page);
            return;
        }

        $this->loadCategoryPath($this->sellable->getCategoryID());

        $this->item = $this->createDetailsItem();

        //one address per product: /products/<prodID>/<current name slug>/ - other addresses get a 301
        Module::RedirectCanonical($this->item->getURL());
        $page->setContentURL($this->item->getURL());
        $page->head()->addCanonicalParameter("prodID");
        $page->head()->addCanonicalParameter("product_name");

        $prodID = $this->sellable->getProductID();
        register_shutdown_function(function() use ($prodID) {
            ProductDetailsContent::UpdateViewCounter($prodID);
        });

        $page->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/product_details.css");
        $page->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/ProductListItem.css");

        //the main landmark of the page
        if ($page instanceof StorePage) {
            $page->getMainSection()->content()->setTagName("main");
            $page->getMainSection()->content()->setRole("main");
        }

        $conversion = Marketing::Conversion(GTMConvParam::VIEW_PDP);
        if ($conversion) $page->head()->addScript($conversion);

        $viewItemEvent = new GTMViewItemEvent();
        $viewItemEvent->setSellable($this->sellable);
        $page->head()->addScript($viewItemEvent);

        $this->cmp->items()->append($this->breadcrumb);
        $this->cmp->items()->append($this->item);
        $this->initProductTapes();

        $this->item->setCategories($this->getCategoryPath());
        $this->item->initialize();
        $this->fillBreadCrumb();

        //the SEO title of the product, else the title rule (productTitle)
        $this->config->title = ($this->sellable->getSeoTitle() !== "") ? $this->sellable->getSeoTitle() : $this->productTitle();
        $page->setMetaDescription($this->productDescription());
    }

    /**
     * The product is not available: 404, not indexed, a message in the page
     * @param SparkPage $page
     * @return void
     */
    protected function notFound(SparkPage $page) : void
    {
        http_response_code(404);
        $page->head()->addMeta("robots", "noindex");
        $this->config->title = "Продуктът не е намерен";
        $message = new TextComponent("", "description");
        $message->setTagName("div");
        $message->setText("Този продукт е недостъпен.");
        $this->cmp->items()->append($message);
    }

    /**
     * The details item of the product - a site returns its own
     * @return ProductDetailsItem
     */
    protected function createDetailsItem() : ProductDetailsItem
    {
        return new ProductDetailsItem($this->sellable);
    }

    /**
     * The tapes under the product - a site adds its own
     * @return void
     */
    protected function initProductTapes() : void
    {
        $this->cmp->items()->append(new TapeSameCategory($this->sellable));
    }

    /**
     * The page title - the product title; a site sets its own rule
     * @return string
     */
    protected function productTitle() : string
    {
        return $this->sellable->getTitle();
    }

    /**
     * The meta description: the seo description, else the description, else the title
     * @return string
     */
    protected function productDescription() : string
    {
        $description = $this->sellable->getTitle();
        if ($this->sellable->getSeoDescription()) {
            $description = $this->sellable->getSeoDescription();
        }
        else if ($this->sellable->getDescription()) {
            $description = $this->sellable->getDescription();
        }
        return trim($description);
    }

    /**
     * The breadcrumb ends with the product
     * @return array
     */
    protected function constructPathActions() : array
    {
        $actions = parent::constructPathActions();
        $actions[] = new Action($this->sellable->getTitle(), $this->item->getURL()->toString(), array());
        return $actions;
    }

    /**
     * The og / twitter product tags - after the frame finalized the title
     * @param SparkPage $page
     * @return void
     */
    public function finalizeHead(SparkPage $page) : void
    {
        if (!($this->sellable instanceof SellableItem) || !($this->item instanceof ProductDetailsItem)) return;

        $page->head()->addOGTag("type", "product");
        //Facebook product tags: 'product:' without 'og:'
        $page->head()->addPropertyTag("product:price:amount", sprintf("%0.2f", $this->sellable->getPriceInfo()->getSellPrice()));
        $page->head()->addPropertyTag("product:price:currency", Spark::Get(StoreConfig::DEFAULT_CURRENCY));

        $main_photo = $this->sellable->getMainPhoto();
        if ($main_photo instanceof StorageItem) {
            $width = $this->item->getGallery()->getPhotoWidth();
            $height = $this->item->getGallery()->getPhotoHeight();
            $imageURL = $main_photo->hrefImage($width, $height)->fullURL();
            //no image:width / image:height - the size and the box are bounds, the real size follows the photo
            $page->head()->addOGTag("image", $imageURL);
            $page->head()->addOGTag("image:alt", $this->sellable->getTitle());
            $page->head()->addMeta("twitter:card", "summary_large_image");
            $page->head()->addMeta("twitter:image", $imageURL);
        }
    }

    public function getSellable() : ?SellableItem
    {
        return $this->sellable;
    }

    public function getDetailsItem() : ?ProductDetailsItem
    {
        return $this->item;
    }

    public static function UpdateViewCounter(int $prodID) : void
    {
        Debug::ErrorLog("Updating view counter for prodID: " . $prodID);
        try {
            $insert = SQLInsert::Table("product_view_log");
            $insert->set("prodID", $prodID);
            $insert->set("view_counter", 1);
            $insert->set("order_counter", 0);
            $insert->on(" DUPLICATE KEY UPDATE view_counter=view_counter+1");
            $query = new DBQuery();
            $query->exec($insert);
        }
        catch (Exception $e) {
            Debug::ErrorLog("Unable to increment view counter: " . $e->getMessage());
        }
    }
}

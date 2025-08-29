<?php
include_once("components/Component.php");
include_once("store/utils/SellableItem.php");

include_once("store/beans/ProductFeaturesBean.php");
include_once("store/beans/ProductPhotosBean.php");


include_once("store/responders/json/QueryProductFormResponder.php");
include_once("store/responders/json/OrderProductFormResponder.php");
include_once("store/responders/json/NotifyInstockFormResponder.php");

include_once("store/utils/tbi/TBIFusionPaymentButton.php");
include_once("store/utils/tbi/TBICreditPaymentButton.php");
include_once("store/utils/unicr/UniCreditPaymentButton.php");

include_once("store/components/SellableImageGallery.php");
include_once("store/components/DetailsSidePane.php");

class DetailsTab extends Container
{

    protected ?Container $content = null;

    public function __construct()
    {
        parent::__construct();
        $this->setTagName("section");
        $this->setComponentClass("item");
        $this->content = new Container(false);
        $this->content->setComponentClass("contents");
        $this->items()->append($this->content);
        $this->getCaptionComponent()->setTagName("h3");
    }

    public function getContent() : Container
    {
        return $this->content;
    }

}



class ProductDetailsItem extends Container implements IHeadContents
{

    protected array $categories = array();
    protected URL $url;

    /**
     * @var SellableItem|null
     */
    protected SellableItem $sellable;

    protected ?SellableImageGallery $gallery = null;
    protected ?DetailsSidePane $side_pane = null;

    protected ?Container $tabs = null;

    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = STORE_LOCAL . "/css/ProductDetailsItem.css";
        return $arr;
    }

    public function requiredScript(): array
    {
        $arr = parent::requiredScript();
        $arr[] = STORE_LOCAL . "/js/SellableItem.js";
        $arr[] = STORE_LOCAL . "/js/ProductDetailsItem.js";
        $arr[] = SPARK_LOCAL . "/js/SwipeListener.js";
        return $arr;
    }

    public function __construct(SellableItem $item)
    {
        parent::__construct();

        $this->sellable = $item;

        $this->url = new ProductURL();
        $this->url->setData(array("prodID"=>$this->sellable->getProductID(),"product_name"=>$this->sellable->getTitle()));

        $this->setAttribute("productID", $this->sellable->getProductID());

        $this->gallery = new SellableImageGallery($this->sellable);
        $this->gallery->getImagePopup()->image()->setPhotoSize(640,640);
        $this->items()->append($this->gallery);

        $this->side_pane = new DetailsSidePane($this->sellable);
        $this->side_pane->setParent($this);
        $this->items()->append($this->side_pane);

        $this->tabs = new Container(false);
        $this->tabs->setComponentClass("tabs");
        $this->items()->append($this->tabs);

        $this->initializeCartButtons();
        $this->initializePaymentButtons();

        $this->initFeaturesTab($this->tabs);
        $this->initDescriptionTab($this->tabs);
        $this->initHowToOrderTab($this->tabs);

        $this->setCacheable(true);
    }

    public function getGallery() : SellableImageGallery
    {
        return $this->gallery;
    }

    public function getURL() : URL
    {
        return $this->url;
    }

    public function getCacheName(): string
    {
        $result = parent::getCacheName()."-".$this->sellable->getProductID();
        return $result;
    }

    public static function CleanCacheEntry(int $prodID) : void
    {

        $componentEntryName = basename(ProductURL::$urlProduct)."-".ProductDetailsItem::class."--".$prodID;
        $entryName = ProductDetailsItem::class . "-" . sparkHash($componentEntryName);

        $cacheEntry = CacheFactory::PageCacheEntry($entryName);
        $cacheEntry->remove();

    }

    /**
     * Post CTOR initialization. Call before startRender of page class
     * @return void
     */
    public function initialize() : void
    {
        //init buttons first
        $this->side_pane->initialize();

        $linkedData = $this->initializeLinkedData();
        if ($linkedData instanceof LinkedData) {
            $script = new LDJsonScript();
            $script->setLinkedData($linkedData);
            SparkPage::Instance()->head()->addScript($script);
        }

    }

    /**
     * Initialize structured LinkedData by calling SellableDataParaser::linkedData
     * @return LinkedData
     */
    protected function initializeLinkedData() : LinkedData
    {
        $parser = SellableItem::GetDefaultDataParser();
        return $parser->linkedData($this->sellable, $this->url);
    }

    /**
     * Initialize and enable cart buttons needed
     * @return void
     */
    protected function initializeCartButtons() : void
    {
        $this->side_pane->initializeCartButtons();
    }

    /**
     * Initialize and enable additional payment/credit buttons
     * @return void
     */
    protected function initializePaymentButtons() : void
    {
        $this->side_pane->initializePaymentButtons();
    }

    public function sidePane() : DetailsSidePane
    {
        return $this->side_pane;
    }

    public function setCategories(array $categores) : void
    {
        $this->categories = $categores;
    }

//    public function setURL(URL $url): void
//    {
//        $this->url = $url;
//    }


    protected function initFeaturesTab(Container $tabs) : void
    {

        $features = new ProductFeaturesBean();
        $qry = $features->queryField("prodID", $this->sellable->getProductID());
        $qry->select->fields()->set("feature");
        $num = $qry->exec();
        if ($num) {

            $tab = new DetailsTab();
            $tab->setCaption(tr("Свойства"));
            $tab->addClassName("features");
            $tab->getContent()->buffer()->start();
            echo "<ul>";
            while ($data = $qry->nextResult()) {
                echo "<li>";
                echo $data->get("feature");
                echo "</li>";
            }
            echo "</ul>";
            $tab->getContent()->buffer()->end();
            $tabs->items()->append($tab);
        }

    }

    protected function initDescriptionTab(Container $tabs): void
    {
        if ($this->sellable->getDescription()) {
            $tab = new DetailsTab();
            $tab->setCaption(tr("Описание"));
            $tab->addClassName("description");

            $tab->getContent()->addClassName("long_description");
            $tab->getContent()->setContents($this->sellable->getDescription());
            $tabs->items()->append($tab);
        }
    }

    protected function initHowToOrderTab(Container $tabs): void
    {

        $config = ConfigBean::Factory();
        $config->setSection("store_config");
        $text = $config->get("products_howtoorder", "");
        if ($text) {
            $tab = new DetailsTab();
            $tab->setClassName("description");
            $tab->addClassName("howtoorder");
            $tab->setCaption(tr("Как да поръчам?"));
            $tab->getContent()->addClassName("long_description");
            $tab->getContent()->setContents($text);
            $tabs->items()->append($tab);
        }

    }


}

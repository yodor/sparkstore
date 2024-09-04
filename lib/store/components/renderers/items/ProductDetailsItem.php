<?php
include_once("components/Component.php");

include_once("store/beans/ProductFeaturesBean.php");
include_once("store/beans/ProductPhotosBean.php");

include_once("store/utils/SellableItem.php");
include_once("store/responders/json/QueryProductFormResponder.php");
include_once("store/responders/json/OrderProductFormResponder.php");
include_once("store/responders/json/NotifyInstockFormResponder.php");

include_once("store/utils/tbi/TBICreditPaymentButton.php");
include_once("store/utils/unicr/UniCreditPaymentButton.php");

class ProductDetailsItem extends Component implements IHeadContents,  IPhotoRenderer
{

    const BUTTON_QUERY_PRODUCT = "Query Product";
    const BUTTON_NOTIFY_INSTOCK = "Notify Instock";
    const BUTTON_PHONE_ORDER = "Phone Order";
    const BUTTON_FAST_ORDER = "Fast Order";
    const BUTTON_CART_ORDER = "Cart Order";

    const BUTTON_PAYMENT_TBI = "TBI";
    const BUTTON_PAYMENT_UNICREDIT = "UNICREDIT";

    protected $categories = array();
    protected $url = "";

    /**
     * @var SellableItem|null
     */
    protected $sellable = null;

    //main photo size
    protected $width = -1;
    protected $height = -1;

    protected $side_pane = null;

    protected array $buttons = array();

    //TBI store UID if defined
    protected array $crpayments = array();

    public function __construct(SellableItem $item)
    {
        parent::__construct();

        $this->setAttribute("itemscope","");
        $this->setAttribute("itemtype", "http://schema.org/Product");

        $this->setAttribute("productID", $item->getProductID());

        $this->sellable = $item;

        $this->setPhotoSize(640,640);

        $this->side_pane = new Container(false);
        $this->side_pane->setComponentClass("side_pane");


        $this->initializeCartButtons();
        $this->initializePaymentButtons();
        $this->setCacheable(true);

    }

    public function getCacheName(): string
    {
        return parent::getCacheName()."-".$this->sellable->getProductID();
    }

    /**
     * Initialize and enable cart buttons needed
     * @return void
     */
    protected function initializeCartButtons() : void
    {
        $this->setButtonEnabled(self::BUTTON_QUERY_PRODUCT, true);
        $this->setButtonEnabled(self::BUTTON_NOTIFY_INSTOCK, true);
        $this->setButtonEnabled(self::BUTTON_FAST_ORDER, true);
        $this->setButtonEnabled(self::BUTTON_PHONE_ORDER, true);
        $this->setButtonEnabled(self::BUTTON_CART_ORDER, true);
    }

    /**
     * Initialize and enable additional payment/credit buttons
     * @return void
     */
    protected function initializePaymentButtons() : void
    {
        $this->crpayments[self::BUTTON_PAYMENT_UNICREDIT] = new UniCreditPaymentButton($this->sellable);
        $this->crpayments[self::BUTTON_PAYMENT_TBI] = new TBICreditPaymentButton($this->sellable);
    }

    public function setButtonEnabled(string $button_name, bool $mode)
    {
        if ($mode) {
            $this->buttons[$button_name] = true;
            switch ($button_name) {
                case self::BUTTON_QUERY_PRODUCT:
                    $this->buttons[$button_name] = new QueryProductFormResponder($this->sellable);
                    break;
                case self::BUTTON_NOTIFY_INSTOCK:
                    $this->buttons[$button_name] = new NotifyInstockFormResponder($this->sellable);
                    break;
                case self::BUTTON_FAST_ORDER:
                    $this->buttons[$button_name] = new OrderProductFormResponder($this->sellable);
                    break;
            }
        }
        else {
            if (isset($this->buttons[$button_name])) {
                unset($this->buttons[$button_name]);
            }
        }
    }

    public function isButtonEnabled(string $button_name)
    {
        return isset($this->buttons[$button_name]);
    }

    public function setPaymentEnabled(string $payment_name, bool $mode)
    {
        if ($mode) {

            switch ($payment_name) {
                case self::BUTTON_PAYMENT_TBI:
                    $this->crpayments[$payment_name] = new TBICreditPaymentButton($this->sellable);
                    break;
                case self::BUTTON_PAYMENT_UNICREDIT:
                    $this->crpayments[$payment_name] = new UniCreditPaymentButton($this->sellable);
                    break;

            }
        }
        else {
            if (isset($this->crpayments[$payment_name])) {
                unset($this->crpayments[$payment_name]);
            }
        }
    }

    public function isPaymentEnabled(string $button_name)
    {
        return isset($this->crpayments[$button_name]);
    }

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

    public function setPhotoSize(int $width, int $height): void
    {
        $this->width = $width;
        $this->height = $height;

        if ($this->sellable) {
            $this->sellable->setPhotoSize($width, $height);
        }
    }

    public function getPhotoWidth(): int
    {
        return $this->width;
    }

    public function getPhotoHeight(): int
    {
        return $this->height;
    }

    public function setSellable(SellableItem $item)
    {
        $this->sellable = $item;
    }

    public function setCategories(array $categores)
    {
        $this->categories = $categores;
    }

    public function setURL(string $url)
    {
        $this->url = $url;
    }

    protected function renderImagePane()
    {


        echo "<div class='images'>";

        $gallery_items = $this->sellable->galleryItems();
        $max_pos = count(array_keys($gallery_items));

        $class_add = "";
        $discount_label = "";
        if ($this->sellable->isPromotion()) {
            $class_add = "promo";
            $discount_label = "Промо";
        }
        $stock_amount = $this->sellable->getStockAmount();
        if ($stock_amount<1) {
            $class_add = "nostock";
            $discount_label = "Изчерпан";
        }

        echo "<div class='image_preview {$class_add}' max_pos='$max_pos'>";

            echo "<div class='discount_label'>";
            echo $discount_label;
            echo "</div>";

            $product_name = $this->sellable->getTitle();
            $si = $gallery_items[0];

            $image_popup = new ImagePopup();
            $image_popup->setBean(new ProductPhotosBean());
            $image_popup->setPhotoSize($this->width, $this->height);
            //no relation here
            $image_popup->setAttribute("title", $product_name);
            $image_popup->setAttribute("itemprop", "image");
            //do not set relation here but use list relation targeting items in hte image_gallery
            $image_popup->setAttribute("list-relation", "ProductGallery");
            $image_popup->setID($si->id);
            $image_popup->setLazyLoadEnabled(false);
            $image_popup->getImage()->setAttribute("fetchpriority","high");
            $image_popup->render();

            if ($max_pos>1) {
                echo "<a href='javascript:prev();' class='arrow prev'></a>";
                echo "<a href='javascript:next();' class='arrow next'></a>";
            }

            echo "<div class='blend'></div>";
        echo "</div>"; //image_preview

        //image galleries per color
        echo "<div class='image_gallery'>";
            $class_single = "";
            if ($max_pos<=1) $class_single = "single";
            echo "<div class='list $class_single'>";

            $pos = 0;

            $active = "active=1";
            $si = new StorageItem();
            $si->className = "ProductPhotosBean";

            foreach ($gallery_items as $key=>$si) {
                $ppID = $si->id;
                //$image_popup->setID($ppID);

                echo "<div class='item' itemClass='ProductPhotosBean' itemID='$ppID' pos='$pos' onClick='javascript:galleryItemClicked(this)' relation='ProductGallery' $active>";
                if ($si instanceof StorageItem) {
                    $src = $si->hrefThumb(64);
                    echo "<img src='$src' alt='$product_name' loading='lazy' title='$product_name'>";
                }
                echo "</div>";

                $active="";
                $pos++;
            }

            echo "</div>";//list
        echo "</div>";//image_gallery

        echo "</div>"; // images
    }

    protected function renderGroupCaption()
    {
        echo "<div class='group caption'>";

            echo "<div class='item product_name'>";
                echo "<h1 itemprop='name' class='value'>". $this->sellable->getTitle() . "</h1>";
            echo "</div>";

        echo "</div>";//group caption
    }




    protected function renderGroupDetails()
    {

        echo "<div class='group details'>";

//            $brand_name = $this->sellable->getBrandName();
//            if ($brand_name) {
//                echo "<div class='item brand_name'>";
//                echo "<label>" . tr("Марка") . "</label>";
//                $href = LOCAL . "/products/list.php?brand_name=$brand_name";
//                echo "<a itemprop='brand_name' class='value' href='$href'>$brand_name</a>";
//                echo "</div>";
//            }
//
//            $model = $this->sellable->getModel();
//            if ($model) {
//                echo "<div class='item model'>";
//                echo "<label>" . tr("Модел") . "</label>";
//                echo "<span itemprop='model' class='value'>$model</span>";
//                echo "</div>";
//            }

        echo "</div>"; //details
    }

    protected function renderGroupStockAmount()
    {
        $stock_amount = $this->sellable->getStockAmount();

//        echo "<div class='group stock_amount'>";
//            echo "<div class='item'>";

//            if ($stock_amount>0) {
//                echo "<label>" . tr("В наличност")."</label>";
//            }
//            else {
//                echo "<label>" . tr("Няма наличност")."</label>";
//            }
//                echo "<label>" . tr("Наличност").": </label>";
//                echo "<span class='value'>$stock_amount</span>";
//                echo "<span class='unit'> бр.</span>";

//            echo "</div>";
//        echo "</div>"; //details
    }

    protected function renderGroupAttributes()
    {
        echo "<div class='group attributes'>";
            echo "<div class='viewport'>";
            $attributes = $this->sellable->getAttributes();
            foreach ($attributes as $name=>$value) {
                if ($name && $value) {
                    echo "<div class='item'>";
                    echo "<div class='name'>$name</div><div class='value'>$value</div>";
                    echo "</div>";
                }
            }
            echo "</div>";//viewport
        echo "</div>"; //attributes
    }

    protected function renderGroupVariants()
    {
        echo "<div class='group variants'>";
        echo "<div class='viewport'>";

            $variantNames = $this->sellable->getVariantNames();

            foreach ($variantNames as $idx=>$variantName) {
                echo "<div class='item variant' name='$variantName'>";
                    echo "<div class='name'>$variantName</div><div class='value'></div>";

                    //TODO: listing style
                    $vitem = $this->sellable->getVariant($variantName);
                    if ($vitem instanceof VariantItem) {
                        echo "<div class='list parameters'>";
                        $parameters = $vitem->getParameters();
                        foreach ($parameters as $pos=>$option_value) {
                            $value = attributeValue($option_value);
                            echo "<div class='parameter' pos='$pos' value='$value' onClick='javascript:selectVariantParameter(this)'>$option_value</div>";
                        }
                        echo "</div>"; //parameters
                    }

                echo "</div>"; //item
            }

        echo "</div>";//viewport
        echo "</div>"; //attributes
    }

    protected function renderGroupPricing()
    {

        $priceInfo = $this->sellable->getPriceInfo();
        $stock_amount = $this->sellable->getStockAmount();

        $instock = "no_stock";
        if ($stock_amount>0) {
            $instock = "in_stock='{$stock_amount}'";
        }


        echo "<div class='group pricing' $instock>";

        echo "<div class='item price_info' itemprop='offers' itemscope itemtype='http://schema.org/Offer'>";

        if ($stock_amount>0) {
            echo "<link itemprop='availability' href='https://schema.org/InStock'>";
        }
        else {
            echo "<link itemprop='availability' href='https://schema.org/OutOfStock'>";
        }

        $enabled= ($this->sellable->isPromotion()) ? "" : "disabled";

        echo "<div class='old $enabled'>";
        echo "<span class='value'>" . sprintf("%0.2f", $priceInfo->getOldPrice()) . "</span>";
        echo "&nbsp;<span class='currency'>лв.</span>";
        echo "</div>";

        echo "<div class='sell'>";
        echo "<span class='value' itemprop='price'>" . sprintf("%0.2f", $priceInfo->getSellPrice()) . "</span>";
        echo "<meta itemprop='priceCurrency' content='BGN'>";
        echo "&nbsp;<span class='currency'>лв.</span>";
        echo "</div>";

        echo "</div>"; //price_info

        echo "</div>"; //pricing
    }

    public function renderGroupCartLink()
    {

        $stock_amount = $this->sellable->getStockAmount();

        $instock = "no_stock";
        if ($stock_amount>0) {
            $instock = "in_stock='{$stock_amount}'";
        }

        echo "<div class='group cart_link' $instock>";

            if ($stock_amount<1) {
                if ($this->isButtonEnabled(self::BUTTON_NOTIFY_INSTOCK)) {
                    echo "<a class='button nostock' href='javascript:showNotifyInstockForm()'>";
                    echo "<span class='icon'></span>";
                    echo "<label>" . tr("Уведоми ме при наличност") . "</label>";
                    echo "</a>";
                }
            }
            else {

                if ($this->isButtonEnabled(self::BUTTON_FAST_ORDER)) {
                    echo "<a class='button cart_add fast' href='javascript:showOrderProductForm()'>";
                    echo "<span class='icon'></span>";
                    echo "<label>" . tr("Бърза поръчка") . "</label>";
                    echo "</a>";
                }
                if ($this->isButtonEnabled(self::BUTTON_CART_ORDER)) {
                    echo "<a class='button cart_add' href='javascript:addToCart()'>";
                    echo "<span class='icon'></span>";
                    echo "<label>" . tr("Купи") . "</label>";
                    echo "</a>";
                }
            }


            $config = ConfigBean::Factory();
            $config->setSection("store_config");
            $phone = $config->get("phone_orders", "");
            if ($phone) {
                if ($this->isButtonEnabled(self::BUTTON_PHONE_ORDER)) {
                    echo "<a class='button order_phone' href='tel:$phone'>";
                    echo "<span class='icon'></span>";
                    echo "<label>$phone</label>";
                    echo "</a>";
                }
            }

            if ($this->isButtonEnabled(self::BUTTON_QUERY_PRODUCT)) {
                echo "<a class='button query_product' href='javascript:showProductQueryForm()'>";
                echo "<span class='icon'></span>";
                echo "<label>" . tr("Запитване") . "</label>";
                echo "</a>";
            }

        echo "</div>";
    }

    protected function sidePaneStart()
    {
        $this->side_pane->startRender();
    }

    public function renderSidePane()
    {

        $this->sidePaneStart();

            //title + short description
            $this->renderGroupCaption();

            $this->renderGroupDetails();

            $this->renderGroupStockAmount();

            $this->renderGroupAttributes();

            $this->renderGroupVariants();

            $this->renderGroupPricing();

            $this->renderGroupCartLink();

            echo "<div class='clear'></div>";

            echo "<div class='group credit_payment'>";
            //payment modules
            foreach ($this->crpayments as $idx=>$object) {
                $class = get_class($object);

                if ($object instanceof CreditPaymentButton) {

                    if ($object->isEnabled()) {

                        $buffer = new OutputBuffer();
                        $buffer->start();
                        try {
                            $object->checkStockPrice();
                            $object->renderButton();
                        }
                        catch (Exception $e) {
                            $buffer->clear();
                            debug("Error rendering credit payment button '$class': ".$e->getMessage());
                        }
                        $buffer->end();

                        echo "<div class='module $class'>";
                        echo $buffer->get();
                        echo "</div>";

                    }
                }
                echo "<div class='clear'></div>";

            }

            echo "</div>";

            echo "<div class='clear'></div>";

        $this->sidePaneFinish();
    }

    protected function sidePaneFinish()
    {
        $this->side_pane->finishRender();
    }

    protected function renderFeaturesTab()
    {
        $features = new ProductFeaturesBean();
        $qry = $features->queryField("prodID", $this->sellable->getProductID());
        $qry->select->fields()->set("feature");
        $num = $qry->exec();
        if ($num) {
            echo "<div class='item features'>";
            echo "<h1 class='Caption'>" . tr("Свойства") . "</h1>";
            echo "<div class='contents'>";
            echo "<ul>";
            while ($data = $qry->nextResult()) {
                echo "<li>";
                echo $data->get("feature");
                echo "</li>";
            }
            echo "</ul>";
            echo "</div>"; //contents
            echo "</div>"; //item
        }
    }
    protected function renderDescriptionTab()
    {
        echo "<div class='item description'>";
        echo "<h1 class='Caption'>" . tr("Описание") . "</h1>";

        if ($this->sellable->getDescription()) {
            echo "<div itemprop='description' class='contents long_description'>";
            //echo strip_tags($this->sellable->getDescription(), "<A><P><DIV>");
            echo $this->sellable->getDescription();
            echo "</div>";
        }

        //<iframe width="560" height="315" src="https://www.youtube.com/embed/rTNYMWHrzt4" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>


        echo "</div>"; //item
    }
    protected function renderTabs()
    {
        echo "<div class='tabs'>";

            $this->renderFeaturesTab();
            $this->renderDescriptionTab();


        echo "</div>"; //tabs
    }

    protected function renderImpl()
    {


        echo "<meta itemprop='url' content='".attributeValue($this->url)."'>";

        $content = array();
        foreach ($this->categories as $idx=>$catinfo) {
            $content[] = $catinfo["category_name"];
        }
        $content = implode(" // ",$content);
        if ($content) {
            echo "<meta itemprop='category' content='$content'>";
        }

        $this->renderImagePane();
        $this->renderSidePane();
        $this->renderTabs();


    }


}

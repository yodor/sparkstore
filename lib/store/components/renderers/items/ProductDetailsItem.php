<?php
include_once("components/Component.php");

include_once("store/beans/ProductFeaturesBean.php");
include_once("store/beans/ProductPhotosBean.php");

include_once("store/utils/SellableItem.php");

class ProductDetailsItem extends Component implements IHeadContents,  IPhotoRenderer
{
    protected $categories = array();
    protected $url = "";
    protected $sellable = null;

    //main photo size
    protected $width = -1;
    protected $height = -1;

    public function __construct(SellableItem $item)
    {
        parent::__construct();

        $this->setAttribute("itemscope","");
        $this->setAttribute("itemtype", "http://schema.org/Product");

        $this->sellable = $item;

        $this->setPhotoSize(640,640);

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
        return $arr;
    }

    public function setPhotoSize(int $width, int $height)
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

            echo "<div class='image_preview'>";

                $product_name = $this->sellable->getTitle();
                $main_photo = $this->sellable->getMainPhoto();
                $photo_href = "";
                $photo_class = "";
                $photo_id = "";

                if ($main_photo instanceof StorageItem) {
                    $photo_href = $main_photo->hrefImage($this->width, $this->height);
                    $photo_class = $main_photo->className;
                    $photo_id = $main_photo->id;
                }

                echo "<a class='ImagePopup' itemClass='{$photo_class}' itemID='{$photo_id}' title='".attributeValue($product_name)."'>";
                    echo "<img itemprop='image' alt='".attributeValue($product_name)."' src='$photo_href'>";
                echo "</a>";


                $piID = $this->sellable->getActiveInventoryID();
                $priceInfo = $this->sellable->getPriceInfo($piID);

                echo "<div class='discount_label'>";
                if ($priceInfo->getDiscountPercent()>0) {
                    echo " -".$priceInfo->getDiscountPercent()."%</div>";
                }
                else {
                    echo tr("Промо");
                }
                echo "</div>";

            echo "</div>"; //image_preview

            //image galleries per color
            echo "<div class='image_gallery'>";
            echo "</div>";

        echo "</div>"; // images
    }

    protected function renderGroupDescription()
    {
        echo "<div class='group description'>";

            echo "<div class='item product_name'>";
                echo "<span itemprop='name' class='value'>". $this->sellable->getTitle() . "</span>";
            echo "</div>";

            if ($this->sellable->getCaption()) {
                echo "<div class='item product_summary'>";
                    echo "<span class='value'>" . stripAttributes($this->sellable->getCaption()) . "</span>";
                echo "</div>";
            }

        echo "</div>";//group product_description
    }

    protected function renderGroupColors()
    {
        echo "<div class='group colors'>";

        echo "<div class='item current_color'>";
        echo "<label>" . tr("Избор на цвят") . "</label>";
        echo "<span class='value'></span>";
        echo "</div>";

        echo "<div class='item color_chooser'>";
        echo "<span class='value'>";
        //            echo "<span class='color_button'></span>";
        echo "</span>";
        echo "</div>";//color_chooser

        echo "</div>"; //group colors
    }

    protected function renderGroupSizing()
    {
        echo "<div class='group sizing' >";

        echo "<div class='item current_size'>";
        echo "<label>" . tr("Избор на размер") . "</label>";
        echo "<span class='value'></span>";
        echo "</div>";

        echo "<div class='item size_chooser' model='size_button'>";
        $empty_label = tr("Избери цвят");
        echo "<span class='value' empty_label='$empty_label'>";
        echo "<div>".$empty_label."</div>";
        echo "</span>";
        echo "</div>"; //size_chooser

        echo "</div>"; //group sizing
    }

    protected function renderGroupAttributes()
    {
        echo "<div class='group attributes' >";

        echo "</div>"; //attributes
    }

    protected function renderGroupStockAmount()
    {
        $piID = $this->sellable->getActiveInventoryID();

        $priceInfo = $this->sellable->getPriceInfo($piID);
        $stock_amount = (int)$priceInfo->getStockAmount();

        if ($stock_amount>0) {
            echo "<link itemprop='availability' href='https://schema.org/InStock'>";
        }
        else {
            echo "<link itemprop='availability' href='https://schema.org/OutOfStock'>";
        }

        echo "<div class='group stock_amount disabled'>";

        echo "<div class='item stock_amount'>";
        echo "<label>" . tr("Наличност")."</label>";
        echo "<span class='value'>".$priceInfo->getStockAmount()."</span>";
        echo "<span class='unit'> бр.</span>";
        echo "</div>";

        echo "</div>"; //stock_amount
    }

    protected function renderGroupPricing()
    {
        $piID = $this->sellable->getActiveInventoryID();

        $priceInfo = $this->sellable->getPriceInfo($piID);
        $stock_amount = (int)$priceInfo->getStockAmount();

        $instock = "no_stock";
        if ($stock_amount>0) {
            $instock = "in_stock='{$stock_amount}'";
        }


        echo "<div class='group pricing' $instock>";

        echo "<div class='item price_info' itemprop='offers' itemscope itemtype='http://schema.org/Offer'>";

        $enabled= ($this->sellable->isPromotion($piID)) ? "" : "disabled";

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
        $piID = $this->sellable->getActiveInventoryID();

        $priceInfo = $this->sellable->getPriceInfo($piID);
        $stock_amount = (int)$priceInfo->getStockAmount();

        $instock = "no_stock";
        if ($stock_amount>0) {
            $instock = "in_stock='{$stock_amount}'";
        }

        echo "<div class='group cart_link' $instock>";

            if ($stock_amount<1) {
                echo "<a class='nostock'>";
                    echo "<span class='icon'></span>";
                    echo "<label>" . tr("Няма наличност") . "</label>";
                echo "</a>";
            }
            else {
                echo "<a class='cart_add' href='javascript:addToCart()'>";
                    echo "<span class='icon'></span>";
                    echo "<label>" . tr("Поръчай") . "</label>";
                echo "</a>";
            }

            $config = ConfigBean::Factory();
            $config->setSection("store_config");
            $phone = $config->get("phone", "");
            if ($phone) {
                echo "<a class='order_phone' href='tel:$phone'>";
                //echo "<label>".tr("Телефон за поръчки")."</label>";
                    echo "<span class='icon'></span>";
                    echo "<label>$phone</label>";
                echo "</a>";
            }



        echo "</div>";
    }

    protected function renderGroupLongDescription()
    {
        if ($this->sellable->getDescription()) {
            echo "<div class='item description long_description'>";
            echo "<div itemprop='description' class='contents'>";
            echo $this->sellable->getDescription();
            echo "</div>";
            echo "</div>"; //item
        }
    }

    public function renderSidePane()
    {
        echo "<div class='side_pane' >";

            //title + short description
            $this->renderGroupDescription();

            $this->renderGroupColors();

            $this->renderGroupSizing();

            $this->renderGroupAttributes();

            $this->renderGroupStockAmount();

            $this->renderGroupPricing();

            $this->renderGroupCartLink();

            echo "<div class='clear'></div>";

            $this->renderGroupLongDescription();

        echo "</div>"; //side_pane
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

    protected function renderTabs()
    {
        echo "<div class='tabs'>";

            $this->renderFeaturesTab();

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

        ?>
        <script type='text/javascript'>

            let sellable = new SellableItem(<?php echo json_encode($this->sellable);?>);

            onPageLoad(function () {

                renderActiveSellable();

            });

        </script>
        <?php
    }


}
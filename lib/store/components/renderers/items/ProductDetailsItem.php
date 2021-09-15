<?php
include_once("components/Component.php");

include_once("store/beans/ProductFeaturesBean.php");
include_once("store/beans/ProductPhotosBean.php");

include_once("store/utils/SellableItem.php");

class ProductDetailsItem extends Component implements IHeadContents
{
    protected $categories = array();
    protected $url = "";
    protected $sellable = null;

    public function __construct(SellableItem $item)
    {
        parent::__construct();

        $this->setAttribute("itemscope","");
        $this->setAttribute("itemtype", "http://schema.org/Product");

        $this->sellable = $item;

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
        $product_name = $this->sellable->getTitle();
        $main_photo = $this->sellable->getMainPhoto();
        $photo_href = "";

        if ($main_photo instanceof StorageItem) {
            $photo_href = $main_photo->hrefImage(640,640);
        }

        echo "<div class='images'>";

            echo "<div class='image_preview'>";
                echo "<a class='ImagePopup' itemClass='{$main_photo->className}' itemID='{$main_photo->id}' title='".attributeValue($product_name)."'>";
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

            echo "</div>";

            //image galleries per color
            echo "<div class='image_gallery'>";
            echo "</div>";

        echo "</div>"; // images
    }

    public function renderSidePane()
    {
        echo "<div class='side_pane' >";

            //title + short description
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

            echo "<div class='group attributes' >";

            echo "</div>"; //attributes


            $piID = $this->sellable->getActiveInventoryID();

            $priceInfo = $this->sellable->getPriceInfo($piID);

            echo "<div class='group stock_amount disabled'>";

                echo "<div class='item stock_amount'>";
                    echo "<label>" . tr("Наличност")."</label>";
                    echo "<span class='value'>".$priceInfo->getStockAmount()."</span>";
                    echo "<span class='unit'> бр.</span>";
                echo "</div>";

            echo "</div>"; //stock_amount

            echo "<div class='group pricing'>";

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

                echo "</div>"; //sell_price

            echo "</div>"; //pricing


            echo "<div class='group cart_link'>";

                echo "<a class='cart_add' href='javascript:addToCart()'>";
                    echo "<span class='icon'></span>";
                    echo "<span>".tr("Поръчай")."</span>";
                echo "</a>";

                $config = ConfigBean::Factory();
                $config->setSection("store_config");
                $phone = $config->get("phone", "");
                if ($phone) {
                    echo "<a class='order_phone' href='tel:$phone'>";
                    //echo "<label>".tr("Телефон за поръчки")."</label>";
                    echo "<span class='icon'></span>";
                    echo "<span>$phone</span>";
                    echo "</a>";
                }

            echo "</div>";

            echo "<div class='clear'></div>";

            if ($this->sellable->getDescription()) {
                echo "<div class='item description long_description'>";
                    echo "<div itemprop='description' class='contents'>";
                        echo $this->sellable->getDescription();
                    echo "</div>";
                echo "</div>"; //item
            }

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
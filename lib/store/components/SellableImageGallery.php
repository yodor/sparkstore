<?php
include_once("components/Container.php");
include_once("components/Image.php");
include_once("components/TextComponent.php");
include_once("components/ImagePopup.php");

include_once("storage/StorageItem.php");

include_once("store/utils/SellableItem.php");


class SellableImageGallery extends Container implements IPhotoRenderer {

    protected SellableItem $sellable;
    protected Container $image_preview;

    protected int $width = 0;
    protected int $height = 0;

    public function __construct(SellableItem $item, bool $chained_component_class = true)
    {
        parent::__construct($chained_component_class);

        $this->sellable = $item;

        $this->setTagName("figure");
        $this->setComponentClass("SellableImageGallery");

        $this->setAttribute("role","region");
        $this->setAttribute("aria-label", "Product image gallery");
        $this->setAttribute("draggable", "false");

        $this->image_preview = new Container(false);
        $this->image_preview->setComponentClass("preview");
        $this->image_preview->setAttribute("draggable", "false");

        $this->items()->append($this->image_preview);

        $blend_pane = new TextComponent();
        $blend_pane->setComponentClass("blend");
        $this->items()->append($blend_pane);

        $label = new TextComponent();
        $label->setComponentClass("discount_label");
        $this->items()->append($label);

        if ($this->sellable->isPromotion()) {

            $discountPercent = $this->sellable->getPriceInfo()->getDiscountPercent();
            if ($discountPercent==0) {
                $discountPercent = 100.00 - ((float)($this->sellable->getPriceInfo()->getSellPrice() / $this->sellable->getPriceInfo()->getOldPrice()) * 100.00);
            }
            if ($discountPercent>0) {
                $discountPercent = round($discountPercent,2);
                $label->setContents(" -" . $discountPercent . "%");
            }
            else {
                $label->setContents("Промо");
            }

            $this->addClassName("promo");
        }


        $stock_amount = $this->sellable->getStockAmount();
        if ($stock_amount<1) {
            $label->setContents("Изчерпан");
            $this->addClassName("nostock");
        }

        $gallery_items = $this->sellable->galleryItems();

        foreach($gallery_items as $pos=>$storageItem) {

            if (! ($storageItem instanceof StorageItem)) throw new Exception("Expected StorageItem gallery element");

            $image_popup = new ImagePopup($storageItem);
            $image_popup->setTagName("span");
            $image_popup->addClassName("item");
            $image_popup->setAttribute("draggable", "false");

            //$image_popup->setTitle($this->sellable->getTitle());
            //use list-relation targeting items in the image_gallery container
//            $this->image_popup->setListRelation("ProductGallery");

            $image = $image_popup->image();
            $image->setPhotoSize($this->width, $this->height);
            $image->setUseSizeAttributes(true);

            $image->setAttribute("alt", "Main view ".($pos+1)." of"." ".$this->sellable->getTitle());


            if ($pos>0) {
                $image->setAttribute("loading", "lazy");
            }
            else {
                $image->setAttribute("fetchpriority","high");
            }

            $image->setAttribute("draggable", "false");
            $this->image_preview->items()->append($image_popup);

        }

        $max_pos = count(array_keys($gallery_items));
        $this->image_preview->setAttribute("max_pos",$max_pos);




    }

    public function requiredScript(): array
    {
        $result =  parent::requiredScript();
        $result[] = Spark::Get(Config::SPARK_LOCAL)."/js/SwipeListener.js";
        $result[] = Spark::Get(StoreConfig::STORE_LOCAL)."/js/ImageSlider.js";
        return $result;
    }

    public function requiredStyle(): array
    {
        $result = parent::requiredStyle();
        $result[] = Spark::Get(StoreConfig::STORE_LOCAL)."/css/SellableImageGallery.css";
        return $result;
    }

    public function finishRender(): void
    {
        parent::finishRender();
        ?>
        <script type='text/javascript'>
            onPageLoad(function() {
                const imageSlider = new ImageSlider();
                imageSlider.setClass(".ProductDetailsItem");
                imageSlider.containerClass = ".SellableImageGallery";
                imageSlider.viewportClass = ".preview";
                imageSlider.autoplayEnabled = false;
                imageSlider.dotStyle = ImageSlider.DOT_STYLE_IMAGE;
                imageSlider.initialize();
            });
        </script>
        <?php

    }

    public function setPhotoSize(int $width, int $height): void
    {
        $this->width = $width;
        $this->height = $height;
        $iterator = $this->image_preview->items()->iterator();
        while ($popup = $iterator->next()) {
            if ($popup instanceof ImagePopup) {
                $popup->image()->setPhotoSize($width, $height);
            }
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
}
?>
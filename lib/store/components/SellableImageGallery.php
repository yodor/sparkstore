<?php
include_once("components/Container.php");
include_once("components/Image.php");
include_once("components/TextComponent.php");
include_once("components/ImagePopup.php");

include_once("storage/StorageItem.php");

include_once("store/utils/SellableItem.php");


class SellableImageGallery extends Container {

    protected SellableItem $sellable;

    protected ImagePopup $image_popup;


    public function __construct(SellableItem $item, bool $chained_component_class = true)
    {
        parent::__construct($chained_component_class);

        $this->sellable = $item;

        $this->setTagName("figure");
        $this->setComponentClass("SellableImageGallery");

        $this->setAttribute("role","region");
        $this->setAttribute("aria-label", "Product image gallery");
        $this->setAttribute("draggable", "false");

        $image_preview = new Container(false);
        $image_preview->setComponentClass("preview");
        $image_preview->setAttribute("draggable", "false");

        $this->items()->append($image_preview);

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

//        $storageItem = $gallery_items[0];

        foreach($gallery_items as $pos=>$storageItem) {
            if (! ($storageItem instanceof StorageItem)) throw new Exception("Expected StorageItem gallery element");

            $this->image_popup = new ImagePopup($storageItem);
            $this->image_popup->addClassName("item");
            $this->image_popup->setAttribute("draggable", "false");

            $this->image_popup->setTitle( $this->sellable->getTitle());
            //use list-relation targeting items in the image_gallery container
//            $this->image_popup->setListRelation("ProductGallery");

            $image = $this->image_popup->image();
            $image->setUseSizeAttributes(true);
            $image->setAttribute("fetchpriority","high");
            $image->setAttribute("alt", "Main view of"." ".$this->sellable->getTitle());
            //$image->setAttribute("loading","lazy");

            $image->setAttribute("draggable", "false");
            $image_preview->items()->append($this->image_popup);

        }

        $max_pos = count(array_keys($gallery_items));
        $image_preview->setAttribute("max_pos",$max_pos);

        $blend_pane = new TextComponent();
        $blend_pane->setComponentClass("blend");
        $this->items()->append($blend_pane);


    }

    public function requiredScript(): array
    {
        $result =  parent::requiredScript();
        $result[] = STORE_LOCAL."/js/BannerSlider.js";
        $result[] = STORE_LOCAL."/js/SellableImageGallerySlider.js";

        return $result;
    }

    public function requiredStyle(): array
    {
        $result = parent::requiredStyle();
        $result[] = STORE_LOCAL."/css/SellableImageGallery.css";
        return $result;
    }

    public function getImagePopup() : ImagePopup
    {
        return $this->image_popup;
    }

    public function finishRender(): void
    {
        parent::finishRender();

    }
}
?>
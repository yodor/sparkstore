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

        $image_preview = new Container(false);
        $image_preview->setComponentClass("preview");
        $this->items()->append($image_preview);

        $label = new TextComponent();
        $label->setComponentClass("discount_label");
        $image_preview->items()->append($label);

        if ($this->sellable->isPromotion()) {
            $label->setContents("Промо");
            $image_preview->addClassName("promo");
        }

        $stock_amount = $this->sellable->getStockAmount();
        if ($stock_amount<1) {
            $label->setContents("Изчерпан");
            $image_preview->addClassName("nostock");
        }


        $gallery_items = $this->sellable->galleryItems();

        $storageItem = $gallery_items[0];

        if (! ($storageItem instanceof StorageItem)) throw new Exception("Expected StorageItem gallery element");

        $this->image_popup = new ImagePopup($storageItem);

        $this->image_popup->setTitle( $this->sellable->getTitle());
        //use list-relation targeting items in the image_gallery container
        $this->image_popup->setListRelation("ProductGallery");

        $image = $this->image_popup->image();
        $image->setUseSizeAttributes(true);
        $image->setAttribute("fetchpriority","high");
        $image->setAttribute("alt", "Main view of"." ".$this->sellable->getTitle());
        //$image->setAttribute("loading","lazy");

        $image_preview->items()->append($this->image_popup);

        $max_pos = count(array_keys($gallery_items));
        $image_preview->setAttribute("max_pos",$max_pos);

        if ($max_pos>1) {
            $action_prev = new Button();
            $action_prev->setComponentClass("arrow");
            $action_prev->setClassName("prev");
            $action_prev->setAttribute("aria-label","Previous image");
            $image_preview->items()->append($action_prev);

            $action_next = new Button();
            $action_next->setComponentClass("arrow");
            $action_next->setClassName("next");
            $action_next->setAttribute("aria-label","Next image");
            $image_preview->items()->append($action_next);
        }

        $blend_pane = new TextComponent();
        $blend_pane->setComponentClass("blend");
        $this->items()->append($blend_pane);

//        $thumbnails = new Container();
//        $thumbnails->setComponentClass("gallery");
        $list = $this->thumbnailsList();
        if ($max_pos<2) {
            $list->addClassName("single");
        }
        //$thumbnails->items()->append($list);

        $this->items()->append($list);
    }

    public function requiredScript(): array
    {
        $result =  parent::requiredScript();
        $result[] = STORE_LOCAL."/js/SellableImageGallery.js";
        return $result;
    }

    public function requiredStyle(): array
    {
        $result = parent::requiredStyle();
        $result[] = STORE_LOCAL."/css/SellableImageGallery.css";
        return $result;
    }

    protected function thumbnailsList() : Container
    {

        $list = new Container(false);
        $list->setComponentClass("list");
        $list->setTagName("ul");

        $product_name = $this->sellable->getTitle();
        $gallery_items = $this->sellable->galleryItems();

        $pos = 0;
        foreach ($gallery_items as $storageItem) {
            if (! ($storageItem instanceof StorageItem)) continue;

            $button = new ImageStorage($storageItem);
            $button->addClassName("item");
            $button->setTagName("button");

            $button->setRelation("ProductGallery");
            $button->setPosition($pos);

            if ($pos == 0) {
                $button->setAttribute("active", "1");
            }

            $button->setAttribute("aria-label", "Another view of"." ".$product_name);
            $image = $button->image();
            $image->setAttribute("alt", "View"." ".($pos+1)." "."of"." ".$product_name);
            $image->setAttribute("loading", "lazy");
            $image->setPhotoSize(64, 64);
            $image->setUseSizeAttributes(true);

            $cmp = new Container(false);
            $cmp->addClassName("thumbnail");
            $cmp->setTagName("li");
            $cmp->items()->append($button);

            $list->items()->append($cmp);
            $pos++;
        }
        return $list;
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
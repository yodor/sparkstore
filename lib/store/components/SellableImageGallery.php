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
        $this->wrapper_enabled = true;
        $this->setComponentClass("images");

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

        $image_preview = new Container(false);
        $image_preview->setComponentClass("image_preview");
        $image_preview->addClassName($class_add);

        $this->items()->append($image_preview);

        $discount_label = new TextComponent($discount_label);
        $discount_label->setComponentClass("discount_label");

        $image_preview->items()->append($discount_label);

        $gallery_items = $this->sellable->galleryItems();

        $storageItem = $gallery_items[0];

        if (! ($storageItem instanceof StorageItem)) throw new Exception("Expected StorageItem gallery element");

        $this->image_popup = new ImagePopup();
        $this->image_popup->image()->setStorageItem($storageItem);
        $this->image_popup->image()->setUseSizeAttributes(true);
        //$this->image_popup->image()->setAttribute("loading","lazy");
        $this->image_popup->image()->setAttribute("fetchpriority","high");

        $this->image_popup->setTitle( $this->sellable->getTitle());

        //use list-relation targeting items in the image_gallery container
        $this->image_popup->setAttribute("list-relation", "ProductGallery");
        $this->image_popup->setAttribute("itemprop", "image");

        $image_preview->items()->append($this->image_popup);

        $max_pos = count(array_keys($gallery_items));
        $image_preview->setAttribute("max_pos",$max_pos);

        if ($max_pos>1) {
            $action_prev = new Action("", "javascript:prev()");
            $action_prev->setComponentClass("arrow");
            $action_prev->setClassName("prev");
            $image_preview->items()->append($action_prev);

            $action_next = new Action("", "javascript:next()");
            $action_next->setComponentClass("arrow");
            $action_next->setClassName("next");
            $image_preview->items()->append($action_next);
        }

        $blend_pane = new TextComponent();
        $blend_pane->setComponentClass("blend");
        $this->items()->append($blend_pane);

        $thumbnails = new Container();
        $thumbnails->setComponentClass("image_gallery");
        $list = $this->thumbnailsList();
        if ($max_pos<2) {
            $list->addClassName("single");
        }
        $thumbnails->items()->append($list);

        $this->items()->append($thumbnails);
    }

    protected function thumbnailsList() : Container
    {

        $list = new Container(false);
        $list->setComponentClass("list");

        $product_name = $this->sellable->getTitle();
        $gallery_items = $this->sellable->galleryItems();

        $pos = 0;
        foreach ($gallery_items as $key=>$storageItem) {
            if (! ($storageItem instanceof StorageItem)) continue;

            $item = new Container(false);
            $item->setComponentClass("item");
            $item->setAttribute("relation", "ProductGallery");
            $item->setAttribute("itemClass", $storageItem->className);
            $item->setAttribute("itemID", $storageItem->id);
            $item->setAttribute("pos", $pos);
            $item->setAttribute("onClick", "javascript:galleryItemClicked(this)");

            if ($pos == 0) {
                $item->setAttribute("active", "1");
            }

            $image = new Image();
            $image->setTitle($product_name);
            $image->setAttribute("loading", "lazy");
            $image->setPhotoSize(64, 64);
            $image->setAttribute("src", $storageItem->hrefThumb(64));
            $image->setUseSizeAttributes(true);

            $item->items()->append($image);

            $list->items()->append($item);
            $pos++;
        }
        return $list;
    }

    public function getImagePopup() : ImagePopup
    {
        return $this->image_popup;
    }
}
?>
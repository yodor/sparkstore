<?php
include_once("components/ImageSlider.php");
include_once("components/TextComponent.php");
include_once("storage/StorageItem.php");
include_once("store/utils/SellableItem.php");

/**
 * The photos of a product (the product page): the core ImageSlider with photo dots, the main photo first
 * (fetchpriority high), the promo / sold out label and the sold out blend.
 *
 *   figure.ImageSlider.SellableImageGallery (+ .promo, .nostock)
 *     .viewport > a.ImagePopup.item > img
 *     .nav-arrow.prev / .next, .dots > .dot.image > img
 *     .blend, .discount_label
 */
class SellableImageGallery extends ImageSlider
{
    protected SellableItem $sellable;

    public function __construct(SellableItem $item)
    {
        parent::__construct();

        $this->sellable = $item;

        $this->setTagName("figure");
        $this->setAttribute("role", "region");
        $this->setAttribute("aria-label", "Product image gallery");
        $this->setAttribute("draggable", "false");

        $this->setDots(SliderDots::IMAGE);
        $this->setPriority(true);

        $blend = new TextComponent();
        $blend->setComponentClass("blend");
        $this->items()->append($blend);

        $label = new TextComponent();
        $label->setComponentClass("discount_label");
        $this->items()->append($label);

        if ($this->sellable->isPromotion()) {

            $discountPercent = $this->sellable->getPriceInfo()->getDiscountPercent();
            if ($discountPercent == 0) {
                $discountPercent = 100.00 - ((float)($this->sellable->getPriceInfo()->getSellPrice() / $this->sellable->getPriceInfo()->getOldPrice()) * 100.00);
            }
            if ($discountPercent > 0) {
                $label->setContents(" -" . round($discountPercent, 2) . "%");
            }
            else {
                $label->setText(tr("Промо"));
            }

            $this->addClassName("promo");
        }

        if ($this->sellable->getStockAmount() < 1) {
            $label->setText(tr("Изчерпан"));
            $this->addClassName("nostock");
        }

        foreach ($this->sellable->galleryItems() as $pos => $storageItem) {

            if (!($storageItem instanceof StorageItem)) throw new Exception("Expected StorageItem gallery element");

            //alt and src matter most for the search engines (variants / colors would go here)
            $popup = $this->addImage($storageItem, null, "View " . ($pos + 1) . " of " . $this->sellable->getTitle());
            //the popup and the slider show the photos without a caption
            $popup->removeAttribute("title");
            $popup->image()->getStorageItem()->setName($this->sellable->getTitle());
            $popup->image()->setUseSizeAttributes(true);
        }
    }

    public function requiredStyle(): array
    {
        $result = parent::requiredStyle();
        $result[] = Spark::Get(StoreConfig::STORE_LOCAL) . "/css/SellableImageGallery.css";
        return $result;
    }
}

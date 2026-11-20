<?php
include_once("utils/ImageSizes.php");

/**
 * The image sizes of the store - the product list items (the cart) and the product page. The core has Tiny, Small
 * and Thumbnail (ImageSize); sparkstore.php enables these with them (ImageSizes).
 */
enum StoreImageSize : int implements IImageSize
{
    //the product list items, the cart
    case Item = 275;
    //the product page, the product feeds
    case Large = 640;

    public function pixels() : int
    {
        return $this->value;
    }
}

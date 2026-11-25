<?php
include_once("store/responders/json/StoreContactFormResponder.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/url/ProductURL.php");
include_once("store/forms/QueryProductForm.php");

/**
 * Product enquiry (product page Query button) - sent to StoreConfig::ORDER_EMAIL like the contact enquiries,
 * with a link to the product, its code and price. Reply-To the visitor's e-mail when given. Nothing is stored.
 */
class QueryProductFormResponder extends StoreContactFormResponder
{
    protected SellableItem $sellable;

    public function __construct(SellableItem $sellable)
    {
        parent::__construct();
        $this->sellable = $sellable;

        $productURL = new ProductURL();
        $productURL->setProductID($sellable->getProductID());
        $productURL->setProductName($sellable->getTitle());

        $details = array(tr("Code") => (string)$sellable->getProductID());
        $price = $sellable->getPriceInfo()->getSellPrice();
        if ($price > 0) $details[tr("Price")] = html_entity_decode(strip_tags(formatPrice($price)), ENT_QUOTES | ENT_HTML5, "UTF-8");
        $this->setAbout($sellable->getTitle(), $productURL, $details);

        $this->mailSubject = tr("Product query at: ") . Spark::Get(Config::SITE_DOMAIN);
        $this->acceptedMessage = "Your request was accepted";
        $this->conversionParam = GTMConvParam::QUERY_PRODUCT;
        $this->leadType = "product_query";
    }

    protected function createForm(): InputForm
    {
        return new QueryProductForm();
    }
}

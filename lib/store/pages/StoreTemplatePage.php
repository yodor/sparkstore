<?php
include_once("class/pages/StorePage.php");
include_once("pages/TemplatePage.php");

/**
 * Storefront page of a module (account, checkout): the site StorePage look with the content of the module path -
 * the module template handling of the core TemplatePage trait.
 *
 * The body gets the path as a css class: 'orders/details' → 'orders_details'.
 * Subclasses put the content in the page in update().
 */
abstract class StoreTemplatePage extends StorePage implements IObserver
{
    use TemplatePage;

    public function __construct()
    {
        parent::__construct();

        $this->pathBodyClass = true;

        $this->templateConstruct();
    }
}
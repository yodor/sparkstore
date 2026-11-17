<?php
include_once("class/pages/ProductListPage.php");
include_once("store/utils/url/CategoryURL.php");
include_once("store/utils/url/ProductListURL.php");
include_once("store/utils/url/ProductSearchURL.php");

/**
 * The keyword search (products module path 'search'): the site list page at /products/search/, its categories at
 * /products/search/<catID>/<slug>/. Not indexed (noindex, follow). Without a keyword: 301 to the list or category.
 * The list and category pages send a keyword search here (ProductListPageBase).
 */
class ProductSearchListPage extends ProductListPage
{
    public function __construct()
    {
        CategoryURL::$urlCategorySlug = ProductSearchURL::$urlProductSearch;
        parent::__construct();
        $this->listAddress = (new ProductSearchURL())->toString();
        $this->head()->addMeta("robots", "noindex, follow");
    }

    public function processInput() : void
    {
        parent::processInput();
        if ($this->keyword_search->isProcessed()) return;

        //nothing searched: the same list without the search
        $current = URL::Requested();
        $path = $current->getScriptName();
        $search = Spark::Get(Config::LOCAL) . ProductSearchURL::$urlProductSearch;
        $rest = str_starts_with($path, $search) ? substr($path, strlen($search)) : "";
        $target = (preg_match("#^[0-9]+/#", $rest)) ? Spark::Get(Config::LOCAL) . "/products/category/" . $rest : (new ProductListURL())->toString();

        $url = new URL($target);
        $url->copyParametersFrom($current);
        $url->remove(KeywordSearch::SUBMIT_KEY);
        foreach ($this->keyword_search->getForm()->inputNames() as $name) {
            $url->remove($name);
        }
        header("Location: " . $url->toString(), true, 301);
        exit;
    }
}

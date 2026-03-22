<?php
include_once("store/components/renderers/items/ProductListItem.php");
include_once("storage/StorageItem.php");

class StoreDetails extends ProductDetails {

    protected LabelSpan $brand;
    public function __construct(ProductListItem $item)
    {
        parent::__construct($item);

        //wrapping label and span
        $this->brand = new LabelSpan();
        $this->brand->setAttribute("itemprop","brand");
        $this->brand->setAttribute("itemscope");
        $this->brand->setAttribute("itemtype", "https://schema.org/Brand");

        $this->brand->setComponentClass("brand_name");
        $this->brand->label()->setTagName("span");
        $this->brand->label()->setComponentClass("label");
        $this->brand->label()->setContents("Марка: ");
        $this->brand->span()->setComponentClass("value");
        $this->brand->span()->setAttribute("itemprop", "name");

        $this->info->items()->append($this->brand);
        $this->info->setRenderEnabled(true);
    }
    public function setData(array $data) : void
    {
        parent::setData($data);
        $this->brand->span()->setContents($this->data["brand_name"]);
    }
    public function collectDataKeys(): array
    {
        $result = parent::collectDataKeys();
        $result[] = "brand_name";
        return $result;
    }
}

class StoreListItem extends ProductListItem
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function CreateDetails(): ProductDetails
    {
        return new StoreDetails($this);
    }

    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = Spark::Get(Config::LOCAL) . "/css/ProductListItem.css";
        return $arr;
    }
}

?>
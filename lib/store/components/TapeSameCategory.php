<?php
include_once("store/components/ProductsTape.php");
include_once("store/beans/SellableProducts.php");
include_once("store/beans/ProductCategoriesBean.php");

class TapeSameCategory extends ProductsTape
{

    public function __construct(SellableItem $sellable, int $limit = 4)
    {
        parent::__construct();

        $bean = new SellableProducts();
        $categories = new ProductCategoriesBean();

        $select = clone $bean->select();
        $select->fields()->setPrefix("sellable_products");
        $select = $categories->selectChildNodesWith($select, "sellable_products", $sellable->getCategoryID());

//        echo $select->getSQL();
        $qry = new SQLQuery($select, "prodID");

//        $qry = $this->bean->queryFull();
//        $qry->select->where()->add("catID", $catID);
        $qry->select->where()->add("stock_amount" , "0", " > ");
        $qry->select->order_by = " rand() ";
        $qry->select->group_by = " prodID ";
        $qry->select->limit = "$limit";

        $this->setIterator($qry);

        $categoryURL = new CategoryURL();
        $categoryURL->setCategoryID($sellable->getCategoryID());
        $categoryURL->setCategoryName($sellable->getCategoryName());
        $this->getAction()->setURL($categoryURL);

        $this->addClassName("same_category");
        $this->setCaption(tr("Още продукти от тази категория"));

        $this->list_item->disableProductLinkedData();
    }

}
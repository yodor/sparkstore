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
        $select->setPrefix("sellable_products");
        $select = $categories->selectChildNodesWith($select, "sellable_products", $sellable->getCategoryID());

//        echo $select->getSQL();
        $qry = new SelectQuery($select, "prodID");

//        $qry = $this->bean->queryFull();
//        $qry->select->where()->add("catID", $catID);
        $qry->stmt->where()->expression("stock_amount > 0");
        $qry->stmt->orderRandom();
        $qry->stmt->group_by = " prodID ";
        $qry->stmt->limit($limit);

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
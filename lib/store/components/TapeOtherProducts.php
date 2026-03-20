<?php
include_once("store/components/ProductsTape.php");
include_once("store/beans/SellableProducts.php");

class TapeOtherProducts extends ProductsTape
{
    public function __construct(SellableItem $item, int $limit = 4)
    {
        parent::__construct();

        $bean = new SellableProducts();
        $qry = $bean->queryFull();
        $qry->stmt->orderRandom();
        $qry->stmt->group_by = " prodID ";
        $qry->stmt->where()->add("stock_amount" , "0", " > ");
        $qry->stmt->limit($limit);

        $this->setCaption(tr("Други продукти"));
        $this->setIterator($qry);

        $this->getAction()->setURL(new ProductListURL());

        $this->addClassName("other_products");

        $this->list_item->disableProductLinkedData();
    }

}
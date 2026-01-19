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
        $qry->select->order_by = " rand() ";
        $qry->select->group_by = " prodID ";
        $qry->select->where()->add("stock_amount" , "0", " > ");
        $qry->select->limit = "$limit";

        $this->setCaption(tr("Други продукти"));
        $this->setIterator($qry);

        $this->getAction()->setURL(new ProductListURL());

        $this->addClassName("other_products");

    }

}

?>
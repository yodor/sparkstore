<?php
include_once("objects/data/GTMCommand.php");

class GTMViewItemEvent extends GTMCommand
{
    public function __construct()
    {
        parent::__construct(GTMCommand::COMMAND_EVENT);
        $this->setType("view_item");

    }
    public function setSellable(SellableItem $sellable)
    {
        $this->addParameter("currency", Spark::Get(StoreConfig::DEFAULT_CURRENCY));
        $this->addParameter("value", $sellable->getPriceInfo()->getSellPrice());
        $items = array(
            "item_id" => $sellable->getProductID(),
            "item_name" => $sellable->getTitle(),
            "affiliation" => Spark::Get(Config::SITE_DOMAIN),
            "index" => 0,
            "item_brand" => $sellable->getBrandName(),
            "item_category" => $sellable->getCategoryName(),
            "price" => $sellable->getPriceInfo()->getSellPrice(),
            "quantity" => 1,
        );

        $this->addParameter("items", $items);
    }
}
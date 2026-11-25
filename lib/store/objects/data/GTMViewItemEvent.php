<?php
include_once("objects/data/GTMCommand.php");
include_once("store/objects/data/GTMItems.php");

class GTMViewItemEvent extends GTMCommand
{
    public function __construct()
    {
        parent::__construct(GTMCommand::COMMAND_EVENT);
        $this->setType("view_item");

    }
    public function setSellable(SellableItem $sellable) : void
    {
        $this->addParameter("currency", Spark::Get(StoreConfig::DEFAULT_CURRENCY));
        $this->addParameter("value", $sellable->getPriceInfo()->getSellPrice());
        //GA4 reads items as a list - the item shape of every store event (GTMItems)
        $this->addParameter("items", array(GTMItems::Item($sellable)));
    }
}
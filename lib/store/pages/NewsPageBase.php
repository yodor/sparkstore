<?php
include_once("class/pages/StorePage.php");

include_once("beans/NewsItemsBean.php");
include_once("components/Publications.php");

class NewsPageBase extends StorePage
{
    protected ?NewsItemsBean $bean = null;

    protected ?Publications $publications = null;

    //module url
    protected ?URL $url = null;

    public function __construct()
    {
        parent::__construct();

        $this->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/news.css");

        $this->bean = new NewsItemsBean();

        $this->url = new URL(Spark::Get(Config::LOCAL) . "/news/index.php");

        $this->publications = new Publications($this->bean, $this->url);

        $aside = new Container(false);
        $aside->setTagName("aside");
        $aside->setComponentClass("column");

        $aside->items()->append($this->publications->getLatest());

        $aside->items()->append($this->publications->getArchive());

        $main = new Container(false);
        $main->setTagName("main");
        $main->setComponentClass("column");
        $main->items()->append($this->publications->getMain());

        $this->items()->append($aside);
        $this->items()->append($main);
    }

    public function initialize() : void
    {
        parent::initialize();
        $this->publications->processInput();

    }


}
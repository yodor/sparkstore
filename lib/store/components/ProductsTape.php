<?php
include_once("components/Component.php");
include_once("components/Action.php");
include_once("iterators/SQLQuery.php");
include_once("store/components/renderers/items/ProductListItem.php");

class ProductsTape extends Container
{

    protected Meta $schemaDescription;
    protected Meta $schemaItemCount;

    protected ?Action $action = null;

    protected ?ProductListItem $list_item = null;

    protected ?SQLQuery $query = null;

    protected static ?ProductListItem $defaultItemRenderer = NULL;

    public static function SetDefaultItemRenderer(ProductListItem $item) : void
    {
        self::$defaultItemRenderer = $item;
    }

    public static function GetDefaultItemRenderer() : ProductListItem
    {
        if (is_null(self::$defaultItemRenderer)) {
            self::$defaultItemRenderer = new ProductListItem();
        }
        return self::$defaultItemRenderer;
    }

    public function __construct()
    {
        parent::__construct(false);
        $this->setComponentClass("ProductsTape");

        $this->setTagName("section");
        $this->setAttribute("itemscope", "");
        $this->setAttribute("itemtype", "https://schema.org/ItemList");

        $this->list_item = ProductsTape::GetDefaultItemRenderer();

        $this->action = new Action();
        $this->action->setAttribute("itemprop", "url");

        //create caption_component
        $this->getCaptionComponent()->setContents("");

        $ul = new ClosureComponent($this->renderItems(...), true, false);
        $ul->setComponentClass("");
        $ul->setTagName("ul");
        $this->items()->append($ul);

        $this->schemaDescription = new Meta();
        $this->schemaDescription->setAttribute("itemprop", "description");
        $this->items()->append($this->schemaDescription);

        $this->schemaItemCount = new Meta();
        $this->schemaItemCount->setAttribute("itemprop", "numberOfItems");
        $this->items()->append($this->schemaItemCount);
    }

    public function setSchemaDescription(string $description) : void
    {
        $this->schemaDescription->setContent($description);
    }
    public function getSchemaDescription() : string
    {
        return $this->schemaDescription->getContent();
    }

    protected function CreateCaption(): Container
    {
        $container = parent::CreateCaption();
        $container->setTagName("h2");
        $container->setAttribute("itemprop", "name");
        $container->items()->append($this->action);
        return $container;
    }

    public function getCacheName() : string
    {
        $result = parent::getCacheName();

        if ($this->query instanceof SQLQuery) {
            $result.="-".$this->query->select->getSQL();
        }
        return $result;
    }

    public function setIterator(SQLQuery $query): void
    {
        $this->query = $query;
    }

    public function setCaption(string $caption): void
    {
        $this->setAttribute("aria-label", $caption);
        $this->action->setAttribute("title", $caption);
        $this->action->setContents($caption);
        $this->setSchemaDescription($caption);
    }

    public function getAction() : Action
    {
        return $this->action;
    }

    public function getListItem() : ProductListItem
    {
        return $this->list_item;
    }

    public function setListItem(ProductListItem $item) : void
    {
        $this->list_item = $item;
    }

    protected function renderItems() : void
    {
        if (!$this->query instanceof SQLQuery) return;

        $numResults = 0;
        if (!$this->query->isActive()) {
            $numResults = $this->query->exec();
        }
        else {
            $numResults = $this->query->count();
        }

        $this->schemaItemCount->setContent($numResults);

        $position = 0;
        while ($row = $this->query->next()) {
            $this->list_item->setPosition($position);
            $this->list_item->setData($row);
            $this->list_item->render();
            $position++;
        }
    }
}

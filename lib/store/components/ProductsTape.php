<?php
include_once("components/Component.php");
include_once("components/Action.php");
include_once("iterators/SQLQuery.php");
include_once("store/components/renderers/items/ProductListItem.php");

class ProductsTape extends Container
{

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
        parent::__construct();

        $this->setTagName("section");

        $this->list_item = ProductsTape::GetDefaultItemRenderer();

        $this->action = new Action();
        $this->action->translation_enabled = false;


        $this->items()->append(new ClosureComponent($this->renderItems(...), false));
    }

    protected function CreateCaption(): Container
    {
        $container = parent::CreateCaption();
        $container->setTagName("H2");
        $container->items()->append($this->action);
        return $container;
    }

    public function getCacheName() : string
    {
        if (!($this->query instanceof SQLQuery)) return parent::getCacheName();

        return parent::getCacheName()."-".$this->query->select->getSQL();

    }

    public function setIterator(SQLQuery $query): void
    {
        $this->query = $query;
    }

    public function setCaption(string $caption): void
    {
        $this->getCaptionComponent()->setContents("");
        $this->action->setAttribute("title", $caption);
        $this->action->setContents($caption);
    }

    /**
     * @return URL
     * @throws Exception
     */
    public function getCaptionURL() : URL
    {
        return $this->action->getURL();
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

        $num = $this->query->exec();

        $position = 0;
        while ($row = $this->query->next()) {
            $position++;
            $this->list_item->setPosition($position);
            $this->list_item->setData($row);
            $this->list_item->render();
        }
    }
}

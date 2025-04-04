<?php
include_once("components/Component.php");
include_once("components/Action.php");
include_once("iterators/SQLQuery.php");
include_once("store/components/renderers/items/ProductListItem.php");

class ProductsTape extends Container
{

    protected ?ProductListItem $list_item = null;

    protected ?SQLQuery $query = null;

    protected static ?ProductListItem $defaultItemRenderer = NULL;

    public static function SetDefaultItemRenderer(ProductListItem $item)
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

        $this->list_item = ProductsTape::GetDefaultItemRenderer();

        $action = new Action();
        $action->translation_enabled = false;
        $action->setClassName("Caption");

        $this->caption_component = $action;

        $this->items()->append(new ClosureComponent($this->renderItems(...), false));
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
        parent::setCaption($caption);
        $this->caption_component->setAttribute("title", $caption);
        $this->caption_component->setContents("<h2>".$caption."</h2>");
    }

    /**
     * @return URL
     * @throws Exception
     */
    public function getCaptionURL() : URL
    {

        if ($this->caption_component instanceof Action) {
            return $this->caption_component->getURL();
        }
        throw new Exception("Incorrect action component");
    }

    public function getListItem() : ProductListItem
    {
        return $this->list_item;
    }

    public function setListItem(ProductListItem $item) : void
    {
        $this->list_item = $item;
    }


    protected function renderItems()
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

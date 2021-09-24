<?php
include_once("components/Component.php");
include_once("components/Action.php");
include_once("iterators/SQLQuery.php");
include_once("store/components/renderers/items/ProductListItem.php");

class ProductsTape extends Component
{

    protected $list_item = null;
    protected $title = "";
    protected $action = null;
    protected $query = null;

    protected static $defaultItemRenderer = NULL;

    public static function SetDefaultItemRenderer(DataIteratorItem $item)
    {
        self::$defaultItemRenderer = $item;
    }

    public static function GetDefaultItemRenderer() : DataIteratorItem
    {
        if (is_null(self::$defaultItemRenderer)) {
            self::$defaultItemRenderer = new ProductListItem();
        }
        return self::$defaultItemRenderer;
    }

    public function __construct(string $title = "")
    {
        parent::__construct();

        $this->list_item = ProductsTape::GetDefaultItemRenderer();


        $this->action = new Action();
        $this->action->translation_enabled = false;
        $this->action->addClassName("Caption");

        $this->setTitle($title);

    }

    public function setIterator(SQLQuery $query)
    {
        $this->query = $query;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
        $this->action->setAttribute("title", $title);
        $this->action->setContents($title);
    }

    public function getTitleAction() : Action
    {
        return $this->action;
    }

    public function getListItem() : ProductListItem
    {
        return $this->list_item;
    }

    public function setListItem(ProductListItem $item)
    {
        $this->list_item = $item;
    }

    protected function renderImpl()
    {
        if ($this->query->exec()>0) {
            if ($this->title) {
                $this->action->render();
            }
            $position = 0;
            while ($row = $this->query->next()) {
                $position++;
                $this->list_item->setPosition($position);
                $this->list_item->setData($row);
                $this->list_item->render();
            }
        }

    }
}
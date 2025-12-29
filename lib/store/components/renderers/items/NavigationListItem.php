<?php
include_once("components/renderers/items/DataIteratorItem.php");
include_once("components/Action.php");
include_once("components/Image.php");
include_once("storage/StorageItem.php");
include_once("store/components/ProductsTape.php");

class NavigationListItem extends DataIteratorItem
{
    protected Action $action;
    protected Component $span;
    protected Image $image;
    protected StorageItem $si;

    protected ProductsTape $tape;

    protected Container $banners;

    public function __construct()
    {
        parent::__construct();
        $this->setTagName("li");
        $this->setComponentClass("");

        $this->action = new Action();
        $this->action->setComponentClass("item");
        $this->action->setAttribute("itemprop", "url");
        $this->items()->append($this->action);

        $this->span = new Component();
        $this->span->setTagName("span");
        $this->span->setComponentClass("Caption");
        $this->span->setAttribute("itemprop", "name");
        $this->action->items()->append($this->span);

//        $this->banners = new ClosureComponent($this->renderBanners(...), true, false);

        $this->banners = new Container(false);
        $this->banners->setComponentClass("banners");

        $this->si = new StorageItem();
        $this->image = new Image();
        $this->image->setAttribute("loading", "lazy");
        $this->image->setStorageItem($this->si);

        $this->action->items()->append($this->banners);

        $this->tape = new ProductsTape();
        $this->tape->setCacheable(true);
        $this->tape->getCaptionComponent()->setRenderEnabled(false);
        $this->items()->append($this->tape);

    }
    public function setCacheable(bool $mode): void
    {
        parent::setCacheable($mode);
        $this->tape->setCacheable($mode);
    }

    public function setData(array $data) : void
    {
        parent::setData($data);
        $this->action->setTitle($this->label);
        $this->action->getURL()->setData($data);

        $this->span->setContents($this->label);

        $this->banners->items()->clear();

        $this->image->setTitle($this->label);

        $imagesKey = $this->si->getValueKey();

        $images = $data[$imagesKey];

        if ($imagesKey && $images) {

            $images = explode(",", $images);
            if (count($images) > 0) {
                foreach ($images as $idx => $imageID) {
                    $this->si->id = $imageID;
                    $this->banners->items()->append(clone $this->image);
                }
            }
        }

        $this->tape->setClassName("item {$this->label}");

    }

    public function getTape() : ProductsTape
    {
        return $this->tape;
    }

    public function getAction() : Action
    {
        return $this->action;
    }

    public function getStorageItem() : StorageItem
    {
        return $this->si;
    }

    public function getImage() : Image
    {
        return $this->image;
    }


}

?>
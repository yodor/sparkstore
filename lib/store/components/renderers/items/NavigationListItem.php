<?php
include_once("components/renderers/items/DataIteratorItem.php");
include_once("components/Action.php");
include_once("components/Image.php");
include_once("storage/StorageItem.php");
include_once("store/components/ProductsTape.php");

class NavigationListItem extends DataIteratorItem
{
    protected Action $action;
    protected Container $container;

    protected Component $span;
    protected Image $image;
    protected StorageItem $si;

    protected ProductsTape $tape;

    protected Container $banners;
    protected Container $viewport;
    protected Container $bannerItem;

    protected bool $sliderEnabled = true;

    public function __construct()
    {
        parent::__construct();
        $this->setTagName("li");
        $this->setComponentClass("NavigationListItem");

        $this->action = new Action();
        $this->action->setAttribute("itemprop", "url");
        $this->items()->append($this->action);

        $this->span = new Component();
        $this->span->setTagName("span");
        $this->span->setComponentClass("Caption");
        $this->span->setAttribute("itemprop", "name");
        $this->action->items()->append($this->span);

        $this->banners = new Container(false);
        $this->banners->setComponentClass("banners");
        $this->items()->append($this->banners);

        $this->viewport = new Container(false);
        $this->viewport->setComponentClass("viewport");
        $this->banners->items()->append($this->viewport);

        $this->bannerItem = new Container(false);
        $this->bannerItem->setTagName("A");
        $this->bannerItem->setComponentClass("item");

        $this->si = new StorageItem();
        $this->image = new Image();
        $this->image->setAttribute("loading", "lazy");
        $this->image->setStorageItem($this->si);

        $this->tape = new ProductsTape();
        $this->tape->setCacheable(true);
        $this->tape->getCaptionComponent()->setRenderEnabled(false);
        $this->items()->append($this->tape);

    }

    public function requiredScript(): array
    {
        $arr = parent::requiredScript();
        $arr[] = STORE_LOCAL . "/js/BannerSlider.js";
        return $arr;
    }

    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = STORE_LOCAL . "/css/NavigationListItem.css";
        return $arr;
    }

    public function setCacheable(bool $mode): void
    {
        parent::setCacheable($mode);
        $this->tape->setCacheable($mode);
    }

    public function setSliderEnabled(bool $mode): void
    {
        $this->sliderEnabled = $mode;
    }

    public function setData(array $data) : void
    {
        parent::setData($data);
        $this->setName($this->label);
        $this->action->setTitle($this->label);
        $this->action->getURL()->setData($data);

        $this->span->setContents($this->label);

        $this->viewport->items()->clear();

        $this->image->setTitle($this->label);

        $this->bannerItem->removeAttribute("href");
        $this->bannerItem->items()->clear();


        $imagesKey = $this->si->getValueKey();

        if ($imagesKey) {
            $images = $data[$imagesKey];

            if ($imagesKey && $images) {

                $images = explode(",", $images);
                if (count($images) > 0) {

                    foreach ($images as $idx => $imageData) {
                        $parts = explode("|", $imageData);
                        $imageID = $parts[0] ?? -1;
                        $link    = $parts[1] ?? ""; // Or any suitable default

                        $this->si->id = $imageID;
                        $this->image->setStorageItem($this->si);
                        if (!$link) {
                            $link = $this->action->getURL();
                        }
                        $this->bannerItem->setAttribute("href", $link);
                        $this->bannerItem->items()->clear();
                        $this->bannerItem->items()->append(clone $this->image);
                        $this->viewport->items()->append(clone $this->bannerItem);
                    }

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

    public function finishRender(): void
    {
        parent::finishRender();
        if ($this->sliderEnabled) {
            if ($this->viewport->items()->count()>1) {
               ?>
                    <script type="text/javascript">
                    onPageLoad(function () {
                        let slider = new BannerSlider();
                        slider.setClass(".NavigationListItem");
                        slider.setName("<?php echo $this->getName();?>");
                        slider.initialize();
                    });
                    </script>
                <?php
            }
        }
    }

}

?>
<?php
include_once("components/renderers/items/DataIteratorItem.php");
include_once("components/Action.php");
include_once("components/Image.php");
include_once("storage/StorageItem.php");
include_once("store/components/ProductsTape.php");

enum BannerEffect : int
{
    case NONE = 0;
    case SLIDE = 1;
    case FADE = 2;

}


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

    const int EFFECT_NONE = 0;
    const int EFFECT_SLIDE = 1;
    const int EFFECT_FADE = 2;

    /**
     * Default effect if no custom effect for each item is assigned
     * @var BannerEffect
     */
    protected BannerEffect $effect = BannerEffect::SLIDE;

    /**
     * Target specific names with different effect
     * @var array<string, BannerEffect>
     */
    protected array $effectForName = array();

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
        $this->bannerItem->setAttribute("draggable", "false");

        $this->si = new StorageItem();
        $this->image = new Image();
        $this->image->setAttribute("loading", "lazy");
        $this->image->setAttribute("draggable", "false");
        $this->image->setStorageItem($this->si);

        $this->tape = new ProductsTape();
        $this->tape->setCacheable(true);
        $this->tape->getCaptionComponent()->setRenderEnabled(false);
        $this->items()->append($this->tape);

    }

    public function requiredScript(): array
    {
        $arr = parent::requiredScript();
        $arr[] = Spark::Get(Config::SPARK_LOCAL) . "/js/SwipeListener.js";
        $arr[] = Spark::Get(StoreConfig::STORE_LOCAL) . "/js/ImageSlider.js";
        $arr[] = Spark::Get(StoreConfig::STORE_LOCAL) . "/js/ImageFader.js";
        return $arr;
    }

    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = Spark::Get(StoreConfig::STORE_LOCAL) . "/css/NavigationListItem.css";
        return $arr;
    }

    public function setCacheable(bool $mode): void
    {
        parent::setCacheable($mode);
        $this->tape->setCacheable($mode);
    }

    public function setBannerEffect(BannerEffect $effect): void
    {
        $this->effect = $effect;
    }

    public function setEffectForName(string $name, BannerEffect $effect): void
    {
        $this->effectForName[$name] = $effect;
    }

    public function setData(array $data) : void
    {
        parent::setData($data);

        $this->setName(Spark::AttributeValue($this->label));

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
                        $this->image->removeAttribute("fetchpriority");
                        $this->image->removeAttribute("loading");
                        if ($idx>0) {
                            $this->image->setAttribute("loading", "lazy");
                        }
                        else {
                            $this->image->setAttribute("fetchpriority", "high");
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
        $this->tape->setSchemaDescription($this->label);

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

    /**
     * Return the count of items that are assigned as banners/images using the $createImagesColumn() iterator
     * @return int
     */
    public function bannersCount() : int
    {
        return $this->bannerItem->items()->count();
    }

    public function finishRender(): void
    {
        parent::finishRender();

        if ($this->viewport->items()->count()>1) {
            $name = $this->getName();

            $effect = $this->effect;
            if (isset($this->effectForName[$name])) {
                $effect = $this->effectForName[$name];
            }

            switch ($effect) {

                case BannerEffect::SLIDE:
                    ?>
                    <script type="text/javascript">
                    onPageLoad(function () {
                        let slider = new ImageSlider();
                        slider.setClass(".NavigationListItem");
                        slider.setName("<?php echo $name;?>");
                        slider.containerClass = ".banners";
                        slider.viewportClass = ".viewport";
                        slider.autoplayEnabled = false;
                        slider.initialize();
                    });
                    </script>
                    <?php
                    break;

                case BannerEffect::FADE:
                    ?>
                    <script type="text/javascript">
                    onPageLoad(function() {
                        let fader = new ImageFader();
                        fader.setClass(".NavigationListItem");
                        fader.setName("<?php echo $name;?>");
                        fader.containerClass = ".banners";
                        fader.viewportClass = ".viewport";
                        fader.initialize();
                        fader.setupFadeDelayed(3000);
                    });
                    </script>
                    <?php
                    break;

                case BannerEffect::NONE:
                    break;
            }
        }

    }

}

?>
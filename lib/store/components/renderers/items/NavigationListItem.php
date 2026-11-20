<?php
include_once("components/renderers/items/DataIteratorItem.php");
include_once("components/Action.php");
include_once("storage/StorageItem.php");
include_once("store/components/ProductsTape.php");
include_once("components/ImageSlider.php");
include_once("utils/url/URL.php");

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
    protected StorageItem $si;

    protected ProductsTape $tape;
    protected Component $tapePlaceholder;

    /**
     * The banners of the item (the core ImageSlider) - slide or fade (BannerEffect)
     * @var ImageSlider
     */
    protected ImageSlider $banners;

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

        $this->banners = new ImageSlider();
        $this->banners->addClassName("banners");
        //the first banner of every item is in view - not lazy
        $this->banners->setPriority(true);
        $this->items()->append($this->banners);

        $this->si = new StorageItem();

        $this->tape = new ProductsTape();
        //random products - every render is different, a cache entry would never be read again
        $this->tape->setCacheable(false);
        $this->tape->getCaptionComponent()->setRenderEnabled(false);
        $this->items()->append($this->tape);

        //stands in for the tape until it is loaded (NavigationList::$eagerItems)
        $this->tapePlaceholder = new Component(false);
        $this->tapePlaceholder->setComponentClass("TapePlaceholder");
        $this->tapePlaceholder->setRenderEnabled(false);
        $this->items()->append($this->tapePlaceholder);

    }

    public function requiredScript(): array
    {
        $arr = parent::requiredScript();
        $arr[] = Spark::Get(StoreConfig::STORE_LOCAL) . "/js/NavigationTapes.js";
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

        $this->span->setText($this->label);

        $this->banners->clear();
        $this->banners->setName("banners-" . $this->getValue());
        $this->applyEffect();

        $imagesKey = $this->si->getValueKey();
        $images = $imagesKey ? (string)($data[$imagesKey] ?? "") : "";
        if ($images !== "") {
            //'<id>|<link>,...' - a banner without its own link goes to the item
            foreach (explode(",", $images) as $imageData) {
                $parts = explode("|", $imageData);
                $this->si->id = (int)($parts[0] ?? -1);
                $link = (string)($parts[1] ?? "");
                $url = ($link !== "") ? new URL($link) : clone $this->action->getURL();
                $this->banners->addImage($this->si, $url, $this->label);
            }
        }

        $this->tape->setClassName("item {$this->label}");
        $this->tape->setSchemaDescription($this->label);

    }

    /**
     * Render a placeholder instead of the tape - storefront/js/NavigationTapes.js loads the tape through
     * '$responder' when the item scrolls into view
     * @param string $responder responder name
     * @param int $listIndex index of the navigation list in the responder
     * @return void
     */
    public function setLazyTape(string $responder, int $listIndex) : void
    {
        $this->tapePlaceholder->setAttribute("responder", $responder);
        $this->tapePlaceholder->setAttribute("list", (string)$listIndex);
        $this->tapePlaceholder->setAttribute("item", (string)$this->getValue());
        $this->tapePlaceholder->setRenderEnabled(true);
    }

    public function clearLazyTape() : void
    {
        $this->tapePlaceholder->setRenderEnabled(false);
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

    /**
     * The banners of the item
     */
    public function getBanners() : ImageSlider
    {
        return $this->banners;
    }

    /**
     * Return the count of items that are assigned as banners/images using the $createImagesColumn() iterator
     * @return int
     */
    public function bannersCount() : int
    {
        return $this->banners->count();
    }

    /**
     * The slider set up of the effect of this item (setEffectForName, else the default)
     */
    protected function applyEffect() : void
    {
        $effect = $this->effectForName[$this->getName()] ?? $this->effect;
        switch ($effect) {
            case BannerEffect::FADE:
                $this->banners->setEffect(SliderEffect::FADE);
                $this->banners->setArrows(false);
                $this->banners->setDots(SliderDots::NONE);
                $this->banners->setAutoplay(true, 4500);
                break;
            case BannerEffect::SLIDE:
                $this->banners->setEffect(SliderEffect::SLIDE);
                $this->banners->setArrows(true);
                $this->banners->setDots(SliderDots::SIMPLE);
                $this->banners->setAutoplay(false);
                break;
            case BannerEffect::NONE:
                $this->banners->setEffect(SliderEffect::SLIDE);
                $this->banners->setArrows(false);
                $this->banners->setDots(SliderDots::NONE);
                $this->banners->setAutoplay(false);
                break;
        }
    }

}

?>
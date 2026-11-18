<?php
include_once("store/components/frame/StoreSection.php");
include_once("components/Action.php");
include_once("components/Marquee.php");
include_once("components/Video.php");
include_once("beans/ConfigBean.php");
include_once("utils/url/URL.php");

/**
 * The header of the store frame: the logo (to the home page), the marquee text of the store config, a video
 * (setVideo - mobilevrati)
 */
class StoreHeader extends StoreSection
{
    protected string $logoHref = "";
    protected bool $marquee = true;
    protected string $video = "";

    public function __construct()
    {
        parent::__construct();
        $this->stylesheet = Spark::Get(StoreConfig::STORE_LOCAL) . "/css/frame/StoreHeader.css";
        $this->addClassName("header");
        $this->content()->setTagName("header");
        $this->setAttribute("itemscope");
        $this->setAttribute("itemtype", "https://schema.org/WPHeader");
    }

    /**
     * The address of the logo link (default: the home page)
     * @param string $href
     * @return $this
     */
    public function setLogoHref(string $href) : static
    {
        $this->logoHref = $href;
        return $this;
    }

    /**
     * Show the marquee text of the store config (default: yes)
     * @param bool $enabled
     * @return $this
     */
    public function setMarquee(bool $enabled) : static
    {
        $this->marquee = $enabled;
        return $this;
    }

    /**
     * A video after the logo - '$src' without the extension (.webm and .mp4 sources); the marquee is off then
     * unless set again
     * @param string $src
     * @return $this
     */
    public function setVideo(string $src) : static
    {
        $this->video = $src;
        $this->marquee = false;
        return $this;
    }

    public function fill(Container $content, StorePageBase $page) : void
    {
        $content->items()->append(StoreHeader::Logo($this->logoHref));

        if ($this->video !== "") {
            $content->items()->append(StoreHeader::Video($this->video));
        }

        if ($this->marquee) {
            $cfg = new ConfigBean();
            $cfg->setSection("store_config");
            $marquee = new Marquee();
            $marquee->setContents($cfg->get("marquee_text"));
            $content->items()->append($marquee);
        }
    }

    /**
     * The logo link
     * @param string $href default: the home page
     * @return Component
     */
    public static function Logo(string $href = "") : Component
    {
        if (!$href) {
            $href = Spark::Get(Config::LOCAL) . "/";
        }
        $link = new Action();
        $link->setTagName("a");
        $link->setAttribute("aria-label", "logo");
        $link->setAttribute("title", "logo");
        $link->setURL(new URL($href));
        $link->setComponentClass("logo");
        return $link;
    }

    /**
     * The header video - '$src' without the extension (default: images/header_logo of the site)
     * @param string $src
     * @return Video
     */
    public static function Video(string $src = "") : Video
    {
        if (!$src) {
            $src = Spark::Get(Config::LOCAL) . "/images/header_logo";
        }
        $video = new Video();
        $video->setAttribute("autoplay");
        $video->setAttribute("playsInline");
        $video->setAttribute("muted");
        $video->setAttribute("loop");
        $video->setAttribute("preload", "auto");
        $video->items()->append(new Source($src . ".webm", 'video/webm'));
        $video->items()->append(new Source($src . ".mp4", 'video/mp4'));
        return $video;
    }
}

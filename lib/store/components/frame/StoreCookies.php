<?php
include_once("store/components/frame/StoreSection.php");
include_once("components/CookiesPanel.php");
include_once("components/Container.php");

/**
 * The cookie consent panel of the store frame: the core panel (CookiesPanel::InfoPanel - the text, the choices, the
 * buttons) in a frame section. The footer 'cookies' icon opens it again (sparkCookies.open()).
 *
 *   aside.section.cookies[accepted][settings] > .content > .info
 */
class StoreCookies extends StoreSection
{
    public function __construct()
    {
        parent::__construct();
        $this->stylesheet = Spark::Get(StoreConfig::STORE_LOCAL) . "/css/frame/StoreCookies.css";
        $this->addClassName("cookies");
        $this->setTagName("aside");
        $this->setAttribute("aria-label", "Cookie Consent");
        $this->setAttribute("role", "dialog");
        $this->setAttribute("tabindex", "0");
    }

    public function fill(Container $content, StorePage $page) : void
    {
        $content->items()->append(CookiesPanel::InfoPanel());
    }

    /**
     * The core panel css, then the store's (the position above the bottom bar)
     */
    public function stylesheets() : array
    {
        return array(CookiesPanel::Stylesheet(), $this->stylesheet);
    }
}

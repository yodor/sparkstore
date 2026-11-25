<?php
include_once("store/components/frame/StoreSection.php");
include_once("components/CookiesPanel.php");
include_once("components/Container.php");

/**
 * The cookie consent panel of the store frame: the core panel (CookiesPanel::InfoPanel - the text, the choices, the
 * buttons) in a frame section. The footer 'cookies' icon opens it again (sparkCookies.open()).
 *
 *   div.section.cookies[role=dialog][accepted][settings] > .content > .info
 */
class StoreCookies extends StoreSection
{
    public function __construct()
    {
        parent::__construct();
        $this->stylesheet = Spark::Get(StoreConfig::STORE_LOCAL) . "/css/frame/StoreCookies.css";
        $this->addClassName("cookies");
        //a div: role 'dialog' is not allowed on an aside
        $this->setTagName("div");
        $this->setAttribute("aria-label", "Cookie Consent");
        $this->setAttribute("role", "dialog");
        $this->setAttribute("tabindex", "0");
    }

    public function fill(Container $content, StorePage $page) : void
    {
        $content->items()->append(CookiesPanel::InfoPanel());
    }

    public function finishRender() : void
    {
        parent::finishRender();
        //shown or hidden as the page is parsed, not when it has loaded
        CookiesPanel::RenderState();
    }

    /**
     * The core panel css, then the store's (the position above the bottom bar)
     */
    public function stylesheets() : array
    {
        return array(CookiesPanel::Stylesheet(), $this->stylesheet);
    }
}

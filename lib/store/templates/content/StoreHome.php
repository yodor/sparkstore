<?php
include_once("templates/TemplateContent.php");
include_once("components/Container.php");
include_once("components/TextComponent.php");
include_once("beans/ConfigBean.php");
include_once("store/components/CategoryNavigation.php");

/**
 * The home of a store site (main module path 'home') - the default of the store; a site sets its own content in
 * its path file main/path/home.php. The store config 'marquee_text' (div.about, line breaks of the admin as <br>),
 * then the top categories with their product tapes (CategoryNavigation; the tapes after the first 'eager' load when
 * scrolled into view).
 *
 * Path file settings (TemplateConfig data):
 *  - eager    tapes in the page, the rest load when scrolled into view; default -1: all
 *  - effect   the banner effect of the categories (BannerEffect name, ex. "FADE"), optional
 *
 * Markup: the body has the class 'home' (main module path), div.about, the CategoryNavigation markup
 */
class StoreHome extends TemplateContent
{
    public function initialize(): void
    {
        $this->cmp = new Container(false);

        $config = ConfigBean::Factory();
        $config->setSection("store_config");
        $about = trim(str_replace("\\r\\n", "<BR>", (string)$config->get("marquee_text")));
        //the admin text is HTML (the store config)
        $text = new TextComponent($about, "about");
        $text->setTagName("div");
        $this->cmp->items()->append($text);

        $navigation = new CategoryNavigation();
        $navigation->initialize();
        $navigation->eagerItems = (int)($this->config->data["eager"] ?? -1);
        foreach (BannerEffect::cases() as $effect) {
            if ($effect->name === ($this->config->data["effect"] ?? "")) $navigation->getItem()->setBannerEffect($effect);
        }
        $this->cmp->items()->append($navigation);

        //the cart 'continue shopping' comes back here
        Session::Set("shopping.list", URL::Requested()->toString());
    }

    public function component(): Component
    {
        return $this->cmp;
    }

    protected function getContentTitle(): string
    {
        //the title of the menu item of the home or the site title
        return "";
    }
}

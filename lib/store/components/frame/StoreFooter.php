<?php
include_once("store/components/frame/StoreSection.php");
include_once("components/Button.php");
include_once("beans/ConfigBean.php");
include_once("utils/url/DynamicPageURL.php");

/**
 * The footer of the store frame (fixed): the social links of the store config, the terms pages, the contacts,
 * the phone for orders
 */
class StoreFooter extends StoreSection
{
    public function __construct()
    {
        parent::__construct();
        $this->stylesheet = Spark::Get(StoreConfig::STORE_LOCAL) . "/css/frame/StoreFooter.css";
        $this->addClassName("footer");
    }

    /**
     * The links of the store footer, in their order: name => array(href, title) - only the links that have an
     * address (the social links and the phone from the store config). A site frame with its own footer shows the same
     * links: terms, contacts, the cookie choice.
     * @return array<string, array{0: string, 1: string}>
     */
    public static function Links() : array
    {
        $items = array(
            "facebook" => "",
            "instagram" => "",
            "youtube" => "",
            "terms" => DynamicPageURL::Group("terms")->toString(),
            "contacts" => Spark::Get(Config::LOCAL) . "/contacts",
            "phone" => "",
            //the cookie choice again (SparkCookies)
            "cookies" => "javascript:document.sparkCookies.open()",
        );

        $cfg = new ConfigBean();
        $cfg->setSection("store_config");
        foreach (array("facebook" => "facebook_url", "instagram" => "instagram_url", "youtube" => "youtube_url") as $name => $setting) {
            $href = trim((string)$cfg->get($setting));
            if ($href) $items[$name] = $href;
        }
        $phone = trim((string)$cfg->get("phone_orders"));
        if ($phone) {
            $items["phone"] = "tel:" . preg_replace("/[^0-9+]/", "", $phone);
        }

        //the titles of the links (their tooltip and accessible name)
        $titles = array("facebook" => "Facebook", "instagram" => "Instagram", "youtube" => "YouTube",
            "terms" => tr("Terms and Conditions"), "contacts" => tr("Contacts"), "phone" => tr("Phone"),
            "cookies" => tr("Cookies"));

        $links = array();
        foreach ($items as $name => $href) {
            if (!$href) continue;
            $links[$name] = array($href, $titles[$name]);
        }
        return $links;
    }

    /**
     * The icons of the store links (StoreIcons.css): a container 'StoreIcons social' with one link per icon - the
     * links of '$names' that have an address, in the order of Links(); all of them without '$names'. A site frame
     * shows them in its own bar (with StoreFooter::IconsStylesheet() in its stylesheets).
     * @param string[]|null $names facebook, instagram, youtube, terms, contacts, phone, cookies
     * @return Container
     */
    public static function Icons(?array $names = null) : Container
    {
        $icons = new Container(false);
        $icons->setComponentClass("StoreIcons");
        $icons->addClassName("social");

        foreach (StoreFooter::Links() as $name => $link) {
            if (!is_null($names) && !in_array($name, $names, true)) continue;
            $item = Button::Action("", $link[0]);
            $item->setComponentClass("slot");
            $item->addClassName($name);
            $item->setTitle($link[1]);
            $item->setAttribute("aria-label", $link[1]);
            $icons->items()->append($item);
        }
        return $icons;
    }

    /**
     * The stylesheet of the icons
     */
    public static function IconsStylesheet() : string
    {
        return Spark::Get(StoreConfig::STORE_LOCAL) . "/css/frame/StoreIcons.css";
    }

    public function stylesheets() : array
    {
        return array_merge(array(StoreFooter::IconsStylesheet()), parent::stylesheets());
    }

    public function fill(Container $content, StorePage $page) : void
    {
        $content->items()->append(StoreFooter::Icons());
    }
}

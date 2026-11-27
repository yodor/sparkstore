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

    public function fill(Container $content, StorePage $page) : void
    {
        $social = new Container(false);
        $social->setComponentClass("social");
        $content->items()->append($social);

        foreach (StoreFooter::Links() as $name => $link) {
            $item = Button::Action("", $link[0]);
            $item->setComponentClass("slot");
            $item->addClassName($name);
            $item->setTitle($link[1]);
            $social->items()->append($item);
        }
    }
}

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

    public function fill(Container $content, StorePage $page) : void
    {
        $social = new Container(false);
        $social->setComponentClass("social");
        $content->items()->append($social);

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
            $href = $cfg->get($setting);
            if ($href) $items[$name] = $href;
        }
        $phone = $cfg->get("phone_orders");
        if ($phone) {
            $items["phone"] = "tel:$phone";
        }

        //the titles of the links (their tooltip and accessible name)
        $titles = array("facebook" => "Facebook", "instagram" => "Instagram", "youtube" => "YouTube",
            "terms" => tr("Terms and Conditions"), "contacts" => tr("Contacts"), "phone" => tr("Phone"),
            "cookies" => tr("Cookies"));

        foreach ($items as $name => $href) {
            if (!$href) continue;
            $item = Button::Action("", $href);
            $item->setComponentClass("slot");
            $item->addClassName($name);
            $item->setTitle($titles[$name]);
            $social->items()->append($item);
        }
    }
}

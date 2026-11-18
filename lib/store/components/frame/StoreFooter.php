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
        $this->addClassName("footer");
    }

    public function fill(Container $content, StorePageBase $page) : void
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

        foreach ($items as $name => $href) {
            if (!$href) continue;
            $item = Button::Action("", $href);
            $item->setComponentClass("slot");
            $item->addClassName($name);
            $item->setTitle($name);
            $social->items()->append($item);
        }
    }
}

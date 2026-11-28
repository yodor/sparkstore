<?php
include_once("store/components/frame/StoreSection.php");
include_once("components/Button.php");
include_once("beans/ConfigBean.php");
include_once("beans/DynamicPagesBean.php");
include_once("utils/url/DynamicPageURL.php");

/**
 * The page footer of the store frame (after the content of every page): two columns - the links to the pages marked
 * 'footer_page', the contacts of the store config (phone, e-mail, address, working hours)
 */
class StorePageFooter extends StoreSection
{
    public function __construct()
    {
        parent::__construct();
        $this->stylesheet = Spark::Get(StoreConfig::STORE_LOCAL) . "/css/frame/StorePageFooter.css";
        $this->addClassName("pageFooter");
        $this->content()->setTagName("footer");
    }

    public function fill(Container $content, StorePage $page) : void
    {
        $columns = new Container(false);
        $columns->setComponentClass("columns");
        $content->items()->append($columns);

        $columnLinks = new Container(false);
        $columnLinks->setComponentClass("column page_links");
        $columns->items()->append($columnLinks);

        $dp = new DynamicPagesBean();
        $query = $dp->query("item_title", "keywords", $dp->key());
        $query->stmt->where()->expression("keywords LIKE '%footer_page%'"); //no binding
        $query->stmt->where()->match("visible", 1);
        $query->exec();
        while ($result = $query->nextResult()) {
            $linkButton = Button::Action($result->get("item_title"), DynamicPageURL::Page((int)$result->get("dpID"), (string)$result->get("item_title"))->toString());
            $linkButton->setComponentClass("item");
            $linkButton->setTitle($result->get("item_title"));
            $columnLinks->items()->append($linkButton);
        }

        $cfg = ConfigBean::Factory();
        $cfg->setSection("store_config");
        $items = array();
        foreach (array("phone" => "phone_text", "email" => "email_text", "location" => "address_text", "working_hours" => "working_hours_text") as $key => $setting) {
            $items[$key] = str_replace("\\r\\n", "<BR>", (string)$cfg->get($setting));
        }

        $columnAddress = new Container(false);
        $columnAddress->setComponentClass("column address");
        $columns->items()->append($columnAddress);

        $space = new Container(false);
        $space->setComponentClass("space");
        $columnAddress->items()->append($space);

        foreach ($items as $key => $val) {
            $item = new Container(false);
            $item->setComponentClass("text");
            $item->addClassName($key);
            $space->items()->append($item);

            $icon = new Component(false);
            $icon->setComponentClass("icon");
            $item->items()->append($icon);

            $value = new Component(false);
            $value->setComponentClass("value");
            $value->setContents($val);
            $item->items()->append($value);
        }
    }
}

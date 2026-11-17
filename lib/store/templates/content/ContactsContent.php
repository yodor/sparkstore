<?php
include_once("templates/TemplateContent.php");
include_once("components/Container.php");
include_once("components/TextComponent.php");
include_once("components/InlineScript.php");
include_once("beans/ConfigBean.php");
include_once("store/beans/ContactAddressesBean.php");
include_once("store/responders/json/StoreContactFormResponder.php");

/**
 * The contacts page of a store site (main module path 'contacts'): the caption, the enquiry button (dialog of
 * StoreContactFormResponder - mailed to the shop, nothing stored), the map and the addresses of the admin
 * (ContactAddressesBean, by position; a click shows the map of the address). Without addresses: the map of the
 * store config 'maps_url'.
 *
 * Path file settings (TemplateConfig data):
 *  - button   the text of the enquiry button, default "Изпрати запитване"
 *
 * Markup (the store css .StorePage.contacts): h1.Caption, a.ColorButton.ContactsButton, div.columns >
 * div.column.map > div.panel.map > iframe#google_map, div.column.addresses > div.details > div.item.city|address|email|phone
 */
class ContactsContent extends TemplateContent
{
    public function initialize(): void
    {
        //the enquiry dialog - its JSON requests are processed with the page
        new StoreContactFormResponder();

        $button = (string)($this->config->data["button"] ?? "Изпрати запитване");

        $this->cmp = new Container(false);

        $caption = new TextComponent("", "Caption");
        $caption->setTagName("h1");
        $caption->setText($this->config->title);
        $this->cmp->items()->append($caption);

        $enquiry = new TextComponent("", "ColorButton ContactsButton");
        $enquiry->setTagName("a");
        $enquiry->setAttribute("onClick", "showContactsForm()");
        $enquiry->setText($button);
        $this->cmp->items()->append($enquiry);

        $addresses = $this->addresses();

        $mapURL = "";
        if (count($addresses) < 1) {
            $config = ConfigBean::Factory();
            $config->setSection("store_config");
            $mapURL = (string)$config->get("maps_url");
        }

        if ($mapURL !== "" || count($addresses) > 0) {
            $this->cmp->items()->append($this->columns($mapURL, $addresses));
        }

        $script = new InlineScript();
        $script->setCode($this->script($button));
        $this->cmp->items()->append($script);
    }

    /**
     * The addresses of the admin by position
     * @return array<array>
     */
    protected function addresses() : array
    {
        $bean = new ContactAddressesBean();
        $query = $bean->queryFull();
        $query->stmt->order("position", OrderDirection::ASC);
        $query->exec();
        $result = array();
        while ($row = $query->next()) {
            $result[] = $row;
        }
        $query->free();
        return $result;
    }

    protected function columns(string $mapURL, array $addresses) : Container
    {
        $columns = new Container(false);
        $columns->setComponentClass("columns");

        $mapColumn = new Container(false);
        $mapColumn->setComponentClass("column map");
        $panel = new Container(false);
        $panel->setComponentClass("panel map");
        $frame = new Component(false);
        $frame->setTagName("iframe");
        $frame->setComponentClass("");
        $frame->setAttribute("id", "google_map");
        $frame->setAttribute("src", $mapURL);
        $frame->setAttribute("frameborder", "0");
        $frame->setAttribute("allowfullscreen", "");
        $frame->setAttribute("tabindex", "0");
        $panel->items()->append($frame);
        $mapColumn->items()->append($panel);
        $columns->items()->append($mapColumn);

        $list = new Container(false);
        $list->setComponentClass("column addresses");
        foreach ($addresses as $row) {
            $list->items()->append($this->details($row));
        }
        $columns->items()->append($list);

        return $columns;
    }

    /**
     * One address: city (text), address (HTML of the admin), e-mail and phone links
     */
    protected function details(array $row) : Container
    {
        $details = new Container(false);
        $details->setComponentClass("details");
        $details->setAttribute("pos", (string)$row["position"]);
        $details->setAttribute("map-url", (string)$row["map_url"]);
        $details->setAttribute("onClick", "updateMap(this);");

        $city = new TextComponent("", "item city");
        $city->setTagName("div");
        $city->setText((string)$row["city"]);
        $details->items()->append($city);

        $address = new TextComponent((string)$row["address"], "item address");
        $address->setTagName("div");
        $details->items()->append($address);

        $email = trim(strip_tags((string)$row["email"]));
        $details->items()->append($this->link("item email", "Email: ", "mailto:", $email));

        $phone = trim(strip_tags((string)$row["phone"]));
        $details->items()->append($this->link("item phone", "Телефон: ", "tel:", $phone));

        return $details;
    }

    protected function link(string $class, string $label, string $scheme, string $value) : Component
    {
        $item = new TextComponent("", $class);
        $item->setTagName("div");
        if ($value !== "") {
            $text = Spark::AttributeValue($value);
            $item->setContents($label . "<a href='" . $scheme . $text . "'>" . $text . "</a>");
        }
        return $item;
    }

    protected function script(string $title) : string
    {
        $title = json_encode($title, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
        return <<<JS
function showContactsForm()
{
    let contacts_dialog = new JSONFormDialog();
    contacts_dialog.setResponder("StoreContactFormResponder");
    contacts_dialog.setTitle($title);
    contacts_dialog.show();
}
function updateMap(elm)
{
    let map = document.querySelector("#google_map");
    if (elm && map) {
        map.setAttribute("src", elm.getAttribute("map-url"));
    }
}
onPageLoad(function(){
    let first = document.querySelector(".details[pos='1']");
    if (first) updateMap(first);
});
JS;
    }

    public function component(): Component
    {
        return $this->cmp;
    }

    protected function getContentTitle(): string
    {
        return "Контакти";
    }
}

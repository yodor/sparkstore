<?php
include_once("pages/SparkPage.php");

include_once("utils/script/LDJsonScript.php");
include_once("utils/menu/BeanMenuFactory.php");

include_once("components/MenuBar.php");
include_once("components/KeywordSearch.php");
include_once("components/ClosureComponent.php");
include_once("components/Marquee.php");
include_once("components/Video.php");
include_once("components/PageSection.php");

include_once("forms/InputForm.php");
include_once("forms/renderers/FormRenderer.php");
include_once("forms/processors/FormProcessor.php");
include_once("input/DataInputFactory.php");

include_once("beans/MenuItemsBean.php");
include_once("store/beans/SectionsBean.php");

include_once("auth/UserAuthenticator.php");

include_once("utils/CurrencyConverter.php");

include_once("store/beans/ProductCategoriesBean.php");

include_once("store/utils/cart/Cart.php");
include_once("beans/DynamicPagesBean.php");
include_once("store/responders/json/VoucherFormResponder.php");
include_once("store/utils/TawktoScript.php");

include_once("dialogs/json/JSONFormDialog.php");
include_once("objects/data/LinkedData.php");

class StorePageBase extends SparkPage
{

    protected ?MenuBar $menu_bar = NULL;

    protected ?KeywordSearch $keyword_search = NULL;

    public string $client_name = "";

    protected ?PageSection $_header = null;
    protected ?PageSection $_menu = null;
    protected ?PageSection $_main = null;
    protected ?PageSection $_footer = null;
    protected ?PageSection $_cookies = null;
    protected ?PageSection $_page_footer = null;

    public bool $vouchers_enabled = false;

    protected function headInitialize() : void
    {


        $this->head()->addMeta("robots", "index, follow, snippet");

        $config = ConfigBean::Factory();
        $config->setSection("seo");

        $this->keywords = sanitizeKeywords($config->get("meta_keywords"));
        $this->description = $config->get("meta_description");

        $facebookID_pixel = $config->get("facebookID_pixel");
        if ($facebookID_pixel) {
            $this->head()->addScript(new FBPixel($facebookID_pixel));
        }

        $gtag = new GTAG();
        $googleID_analytics = $config->get("googleID_analytics");
        if ($googleID_analytics) {
            $gtag->setID($googleID_analytics);
            $this->head()->addScript($gtag);
        }

        $gtag = new GTAG();
        $googleID_ads = $config->get("googleID_ads");
        if ($googleID_ads) {
            $gtag->setID($googleID_ads);
            $this->head()->addScript($gtag);
        }

        $adsID = $config->get("googleID_ads", "");
        $conversionID = $config->get("googleID_ads_conversion", "");
        if ($adsID && $conversionID) {
            $obj = new GTAGObject();
            $obj->setCommand(GTAGObject::COMMAND_EVENT);
            $obj->setType("conversion");
            $obj->setParamTemplate("{'send_to': '%googleID_ads_conversion%'}");
            $obj->setName("googleID_ads_conversion");
            $data = array("googleID_ads_conversion"=>$conversionID);
            $obj->setData($data);

            $this->head()->addScript($obj->script());
        }

        $config->setSection("store_config");
        $phone = $config->get("phone_orders", "");

        $page_id = $config->get("tawkto_id", "");
        if ($page_id) {
            $this->head()->addScript(new TawktoScript($page_id));
        }

        $organization = new LinkedData("Organization");
        $organization->set("name", SITE_TITLE);
        $organization->set("url", SITE_URL);
        $organization->set("logo", SITE_URL."/images/logo_header.svg");
        $contactPoint = new LinkedData("ContactPoint");
        $contactPoint->set("telephone", $phone);
        $contactPoint->set("contactType", "sales");
        $contactPoint->set("areaServed", substr(DEFAULT_LANGUAGE_ISO3, 0, 2));
        $contactPoint->set("availableLanguage", DEFAULT_LANGUAGE);
        $organization->set("contactPoint", $contactPoint->toArray());

        $orgScript = new LDJsonScript();
        $orgScript->setLinkedData($organization);
        $this->head()->addScript($orgScript);

        $website = new LinkedData("WebSite");
        $website->set("name", mb_strtoupper(SITE_TITLE). " - " . tr("Official Page"));
        $website->set("url", SITE_URL);

        $potentialAction = new LinkedData("SearchAction");

        $entryPoint = new LinkedData("EntryPoint");
        $entryPoint->set("urlTemplate", SITE_URL."/products/list.php?filter=search&keyword={keyword}");
        $potentialAction->set("target", $entryPoint->toArray());
        $potentialAction->set("query-input", "required name=keyword");

        $website->set("potentialAction", $potentialAction->toArray());

        $wwwScript = new LDJsonScript();
        $wwwScript->setLinkedData($website);
        $this->head()->addScript($wwwScript);


        $this->head()->addCSS(STORE_LOCAL . "/css/store.css");

        $this->head()->addJS(SPARK_LOCAL."/js/SparkCookies.js");
        $this->head()->addJS(STORE_LOCAL."/js/cookies.js");
        $this->head()->addJS(STORE_LOCAL."/js/menusticky.js");

        $this->head()->addOGTag("url", URL::Current()->fullURL()->toString());
        $this->head()->addOGTag("site_name", SITE_TITLE);
        $this->head()->addOGTag("type", "website");


        $link = new Link();
        $link->setRelation("preconnect");
        $link->setHref("https://fonts.googleapis.com");
        $this->head()->items()->append($link);

        $linkgtag = new Link();
        $linkgtag->setRelation("dns-prefetch");
        $linkgtag->setHref("https://www.googletagmanager.com");
        $this->head()->items()->append($linkgtag);
    }

    protected function selectActiveMenu()
    {

        $main_menu = $this->menu_bar->getMenu();
        $main_menu->selectActive(array(MenuItemList::MATCH_FULL,MenuItemList::MATCH_PARTIAL));

    }

    /**
     * Construct the title tag.
     * Default implementation use the value set as preferred_title property
     * if preferred_title is empty construct title using the selected menu items (getSelectedPath)
     * @return void
     */
    protected function constructTitle() : void
    {
        if (mb_strlen($this->getTitle()) > 0) return;

        $main_menu = $this->menu_bar->getMenu();

        $this->setTitle(constructSiteTitle($main_menu->getSelectedPath()));
    }

    public function __construct()
    {

        $this->auth = new UserAuthenticator();
        $this->loginURL = LOCAL . "/account/login.php";
//
        parent::__construct();
//
        $this->authorize();
//
        if ($this->context) {
            $this->client_name = (string)$this->context->getData()->get(SessionData::FULLNAME);
        }

        //template JSONFormDialog
        new JSONFormDialog();


        $this->headInitialize();

        $factory = new BeanMenuFactory(new MenuItemsBean());
        $this->menu_bar = new MenuBar($factory->menu());
        $this->menu_bar->setName("StorePage");

        $ksc = new KeywordSearch();

        //just initialize the keyword form here. Search fields are initialized in ProductsListPage as form is posted there
        $ksc->getForm()->getInput("keyword")->getRenderer()->input()?->setAttribute("placeholder", "Търси ...");
        $ksc->getForm()->getRenderer()->setAttribute("method", "get");
        $ksc->getForm()->getRenderer()->setAttribute("action", LOCAL . "/products/list.php");
        $ksc->getButton("search")->setComponentClass("");
        $ksc->getButton("search")->setContents("");

        $ksc->getButton("clear")->setRenderEnabled(false);

//        $show_search = new ColorButton();
//        $show_search->setAttribute("action", "show_search");
//        $show_search->setAttribute("onClick", "showSearch()");
//        $ksc->getButtons()->items()->append($show_search);

        $this->keyword_search = $ksc;

        $this->_header = new PageSection();
        $this->_header->addClassName("header");
        $this->_header->setAttribute("itemscope", "");
        $this->_header->setAttribute("itemtype", "http://schema.org/WPHeader");

        $this->_menu = new PageSection();
        $this->_menu->addClassName("menu");

        $this->_main = new PageSection();
        $this->_main->addClassName("main");

        $this->_cookies = new PageSection();
        $this->_cookies->addClassName("cookies");
//        $this->_cookies->setAttribute("accepted", 0);

        $this->_footer = new PageSection();
        $this->_footer->addClassName("footer");

        $this->_page_footer = new PageSection();
        $this->_page_footer->addClassName("pageFooter");


        $this->initHeaderSection($this->_header->content());


        $this->_menu->content()->items()->append($this->menu_bar);

        $menu_groups = new Container(false);
        $menu_groups->setComponentClass("menu_groups");
        $this->initMenuGroups($menu_groups);
        $this->menu_bar->items()->append($menu_groups);


        //each page content is append with this section
        $this->initPageFooterSection($this->_page_footer->content());

        //cookies popup
        $this->_cookies->content()->items()->append($this->createCookiesInfoPanel());


        //page footer popup fixed
        $this->initFooterSection($this->_footer->content());


        if ($this->vouchers_enabled) {
            $voucher_handler = new VoucherFormResponder();
            $this->head()->addJS(STORE_LOCAL."/js/vouchers.js");
        }

    }

    public function getMenuBar(): ?MenuBar
    {
        return $this->menu_bar;
    }

    public function startRender(): void
    {
        //first prepare the menus - can be used from the title tag
        $this->selectActiveMenu();

        parent::startRender();
        //inside body already

        echo "\n<!-- startRender StorePage-->\n";

        $this->_header->render();
        $this->_menu->render();


        $this->_main->startRender();
        $this->_main->spaceLeft()->render();
        $this->_main->content()->startRender();

    }

    public function finishRender(): void
    {
        $this->_main->content()->finishRender();
        $this->_main->spaceRight()->render();
        $this->_main->finishRender();

        $this->_page_footer->render();

        $this->_cookies->render();
        $this->_footer->render();


        echo "\n";
        echo "\n<!-- finishRender StorePage-->\n";

        parent::finishRender();

    }

    protected function createSearchMenuGroup() : ?Container
    {
        $container = new Container(false);
        $container->setComponentClass("group search_pane");
        $container->items()->append($this->keyword_search);
        return $container;
    }

    protected function createCustomerMenuGroup() : ?Container
    {
        $container = new Container(false);
        $container->setComponentClass("group customer_pane");

        $icon_contents = "<span class='icon'></span>";

        $button_account = new Action();
        $button_account->getURL()->fromString(LOCAL . "/account/login.php");
        $button_account->setAttribute("title", tr("Account"));
        $button_account->setClassName("button account");
        $button_account->setContents($icon_contents);
        if ($this->context) {
            $button_account->getURL()->fromString(LOCAL . "/account/");
            $button_account->addClassName("logged");
        }

        $container->items()->append($button_account);

        $button_cart = new Action();
        $button_cart->getURL()->fromString(LOCAL . "/checkout/cart.php");
        $button_cart->setAttribute("title", tr("Cart"));
        $button_cart->addClassName("button cart");

        $button_contents = $icon_contents;
        $cart_items = Cart::Instance()->itemsCount();
        if ($cart_items > 0) {
            $button_cart->setAttribute("item_count", $cart_items);
            $button_contents .= "<span class='items_dot'>$cart_items</span>";
        }
        $button_cart->setContents($button_contents);

        $container->items()->append($button_cart);

        return $container;
    }

    protected function initMenuGroups(Container $container): void
    {
        $search_group = $this->createSearchMenuGroup();
        if ($search_group instanceof Container) {
            $container->items()->append($search_group);
        }

        $customer_group = $this->createCustomerMenuGroup();
        if ($customer_group instanceof Container) {
            $container->items()->append($customer_group);
        }
    }

    protected function createLogo(string $href = LOCAL . "/home.php") : Component
    {
        $link = new Action();
        $link->setTagName("A");

        $link->setURL(new URL($href));
        $link->setComponentClass("logo");
        return $link;
    }

    protected function createVideo(string $src = LOCAL."/images/header_logo") : Video
    {

        $video = new Video();
        $video->setAttribute("autoplay");
        $video->setAttribute("playsInline");
        $video->setAttribute("muted");
        $video->setAttribute("loop");
        $video->setAttribute("preload", "auto");

        $source_webm = new Source($src.".webm", 'video/webm');
        $video->items()->append($source_webm);
        $source_mp4 = new Source($src.".mp4", 'video/mp4');
        $video->items()->append($source_mp4);

        return $video;
    }

    protected function initHeaderSection(Container $container) : void
    {

        $container->items()->append($this->createLogo());

        $cfg = new ConfigBean();
        $cfg->setSection("store_config");

        $marquee = new Marquee();
        $marquee->setContents($cfg->get("marquee_text"));
        $container->items()->append($marquee);

    }

    protected function initPageFooterSection(Container $container): void
    {
        $columns = new Container(false);
        $columns->setComponentClass("columns");
        $container->items()->append($columns);

        $columnVoucher = new Container(false);
        $columnVoucher->setComponentClass("column voucher");
        $columns->items()->append($columnVoucher);

        if ($this->vouchers_enabled) {
            $button = Button::ActionButton(tr("Купи ваучер"), "showVoucherForm()");
            $columnVoucher->items()->append($button);
        }

        $columnLinks = new Container(false);
        $columnLinks->setComponentClass("column page_links");
        $columns->items()->append($columnLinks);

        $dp = new DynamicPagesBean();
        $query = $dp->query("item_title", "keywords", $dp->key());
        $query->select->where()->add("keywords", "'%footer_page%'", " LIKE ", " AND ");
        $query->select->where()->add("visible", 1);
        $num = $query->exec();
        while ($result = $query->nextResult()) {
            $linkButton = Button::Action($result->get("item_title"), new URL(LOCAL."/pages/index.php?id=".$result->get("dpID")));
            $linkButton->setComponentClass("item");
            $linkButton->setTitle($result->get("item_title"));
            $columnLinks->items()->append($linkButton);
        }


        $cfg = ConfigBean::Factory();
        $cfg->setSection("store_config");
        $phone = $cfg->get("phone_text", "");
        $phone = str_replace("\\r\\n", "<BR>", $phone);
        $email = $cfg->get("email_text", "");
        $email = str_replace("\\r\\n", "<BR>", $email);
        $location = $cfg->get("address_text", "");
        $location = str_replace("\\r\\n", "<BR>", $location);
        $working_hours = $cfg->get("working_hours_text", "");
        $working_hours = str_replace("\\r\\n", "<BR>", $working_hours);

        $items = array("phone"=>$phone, "email"=>$email, "location"=>$location, "working_hours"=>$working_hours);

        $columnAddress = new Container(false);
        $columnAddress->setComponentClass("column address");
        $columns->items()->append($columnAddress);

        $space = new Container(false);
        $space->setComponentClass("space");
        $columnAddress->items()->append($space);

        foreach ($items as $key=>$val) {
            $item = new Container(false);
            $item->setComponentClass("text");
            $item->addClassName($key);
            $space->items()->append($item);

            $icon = new Component(false);
            $icon->setComponentClass("icon");
            $item->items()->append($icon);

            $value = new Component(false);
            $value->setComponentClass("value");
            $item->items()->append($value);

            $value->setContents($val);
        }

    }

    protected function initFooterSection(Container $container) : void
    {
        $social = new Container(false);
        $social->setComponentClass("social");
        $container->items()->append($social);

        $items = array(
            "facebook"=>"",
            "instagram"=>"",
            "youtube"=>"",
            "terms"=>LOCAL."/pages/index.php?class=terms",
            "contacts"=>LOCAL."/contacts.php",
            "phone"=>"",
        );

        $cfg = new ConfigBean();
        $cfg->setSection("store_config");
        $facebook_href = $cfg->get("facebook_url", "");
        if ($facebook_href) {
            $items["facebook"] = $facebook_href;
        }
        $instagram_href = $cfg->get("instagram_url", "");
        if ($instagram_href) {
            $items["instagram"] = $instagram_href;
        }
        $youtube_href = $cfg->get("youtube_url", "");
        if ($youtube_href) {
            $items["youtube"] = $youtube_href;
        }
        $phone = $cfg->get("phone_orders", "");
        if ($phone) {
            $items["phone"] = "tel:$phone";
        }

        foreach ($items as $name=>$href) {
            if ($href) {
                $item = Button::Action("", $href);
                $item->setComponentClass("slot");
                $item->addClassName($name);
                $item->setTitle($name);
                $social->items()->append($item);
            }
        }


    }

    protected function createCookiesInfoPanel() : Container
    {
        $container = new Container(false);
        $container->setComponentClass("info");

        $button = Button::Action(tr("Добре"), "javascript:document.sparkCookies.accept()");
        $button->setComponentClass("ColorButton");

        $container->items()->append($button);

        $text = new TextComponent(tr("Сайтът използва '<i>бисквитки</i>', за да подобри услугите. Продължавайки разглеждането му автоматично се съгласявате с тяхното използване."));
        $container->items()->append($text);

        return $container;
    }







}

?>

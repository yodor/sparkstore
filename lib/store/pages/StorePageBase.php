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

include_once("store/utils/CurrencyConverter.php");

include_once("store/beans/ProductCategoriesBean.php");

include_once("store/utils/cart/Cart.php");
include_once("beans/DynamicPagesBean.php");
include_once("store/responders/json/VoucherFormResponder.php");
include_once("store/utils/TawktoScript.php");

include_once("dialogs/json/JSONFormDialog.php");
include_once("objects/data/LinkedData.php");
include_once("store/utils/url/ProductListURL.php");
include_once("store/utils/url/ProductURL.php");
include_once("store/utils/url/CategoryURL.php");
include_once("store/components/ProductsTape.php");
include_once("store/utils/url/ProductListURL.php");
include_once("objects/data/GTMConvParam.php");
include_once("objects/data/GTMConversionCommand.php");
include_once("objects/data/GTMConsentCommand.php");


class StorePageBase extends SparkPage
{

    protected ?Action $home_action = null;

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

    /**
     * Apply additional og and twitter meta data - copy title and description
     * Set og url
     * @return void
     */
    protected function headFinalize(): void
    {
        parent::headFinalize();

        $title = $this->head()->getTitle();
        $this->head()->addOGTag("title", $title);
        $this->head()->addMeta("twitter:title", $title);

        $meta_description = $this->head()->getMeta("description");
        $this->head()->addOGTag("description", $meta_description);
        $this->head()->addMeta("twitter:description", $meta_description);

        $this->head()->addOGTag("url", $this->currentURL()->fullURL()->toString());

    }

    protected function headInitialize() : void
    {
        parent::headInitialize();

        $this->description = "";

        $this->head()->addMeta("robots", "index, follow, snippet");

        $this->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/store.css");

        $this->head()->addJS(Spark::Get(Config::SPARK_LOCAL)."/js/SparkCookies.js");
        $this->head()->addJS(Spark::Get(StoreConfig::STORE_LOCAL)."/js/cookies.js");
        $this->head()->addJS(Spark::Get(StoreConfig::STORE_LOCAL)."/js/menusticky.js");

        $config = ConfigBean::Factory();
        $config->setSection("marketing_config");

        $facebookID_pixel = $config->get("facebookID_pixel");
        if ($facebookID_pixel) {
            $this->head()->addScript(new FBPixel($facebookID_pixel));
        }

        $gtag = new GTAG();
        $this->head()->addScript($gtag);

        $googleID_analytics = $config->get("googleID_analytics");
        if ($googleID_analytics) {
            $cmd = new GTMCommand();
            $cmd->setCommand(GTMCommand::COMMAND_CONFIG);
            $cmd->setType($googleID_analytics);
            $this->head()->addScript($cmd->script());
        }

        $googleID_ads = $config->get("googleID_ads");
        if ($googleID_ads) {
            $cmd = new GTMCommand();
            $cmd->setCommand(GTMCommand::COMMAND_CONFIG);
            $cmd->setType($googleID_ads);
            $this->head()->addScript($cmd->script());
        }

        //default consent
        $default_consent = new GTMConsentCommand();
        $this->head()->addScript($default_consent->script());

        //any page conversion
        $conversionID = $config->get(GTMConvParam::VIEW_ANY_PAGE->value);
        if ($conversionID) {
            $cmd = new GTMConversionCommand($conversionID);
            $this->head()->addScript($cmd->script());
        }

        $config->setSection("store_config");
        $phone = $config->get("phone_orders");

        $page_id = $config->get("tawkto_id");
        if ($page_id) {
            $this->head()->addScript(new TawktoScript($page_id));
        }

        $organization = new LinkedData("Organization");
        $organization->set("name", Spark::Get(Config::SITE_TITLE));
        $organization->set("url", Spark::Get(Config::SITE_URL));
        $organization->set("logo", Spark::Get(Config::SITE_URL)."/images/logo_header.svg");
        $contactPoint = new LinkedData("ContactPoint");
        $contactPoint->set("telephone", $phone);
        $contactPoint->set("contactType", "sales");
        $contactPoint->set("areaServed", substr(Spark::Get(Config::DEFAULT_LANGUAGE_ISO3), 0, 2));
        $contactPoint->set("availableLanguage", Spark::Get(Config::DEFAULT_LANGUAGE));
        $organization->set("contactPoint", $contactPoint->toArray());

        $orgScript = new LDJsonScript();
        $orgScript->setLinkedData($organization);
        $this->head()->addScript($orgScript);

        $website = new LinkedData("WebSite");
        $website->set("name", mb_strtoupper(Spark::Get(Config::SITE_URL)). " - " . tr("Official Page"));
        $website->set("url", Spark::Get(Config::SITE_URL));

        $potentialAction = new LinkedData("SearchAction");

        $entryPoint = new LinkedData("EntryPoint");
        $entryURL = new ProductListURL();
        $entryURL->add(new URLParameter("filter","search"));
        $entryURL->add(new URLParameter("keyword", "{search_term_string}"));
        $entryPoint->set("urlTemplate", $entryURL->fullURL()->toString());
        $potentialAction->set("target", $entryPoint->toArray());
        $potentialAction->set("query-input", "required name=search_term_string");

        $website->set("potentialAction", $potentialAction->toArray());

        $wwwScript = new LDJsonScript();
        $wwwScript->setLinkedData($website);
        $this->head()->addScript($wwwScript);


        $this->head()->addOGTag("site_name", Spark::Get(Config::SITE_TITLE));
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


    /**
     * Use menu
     * @return void
     */
    protected function applyTitleDescription(): void
    {
        $main_menu = $this->menu_bar->getMenu();
        $selectedPath = $main_menu->getSelectedPath();

        //no title is set - try seo title of selected menu or names of selected items

        $title = "";
        $description = "";

        //from top to bottom
        foreach ($selectedPath as $idx=>$selectedItem) {
            if ($selectedItem instanceof MenuItem) {
                if (mb_strlen($selectedItem->getSeoTitle())>0) {
                    $title = $selectedItem->getSeoTitle();
                }
                if (mb_strlen($selectedItem->getSeoDescription())>0) {
                    $description = $selectedItem->getSeoDescription();
                }
            }
        }

        if (!$this->preferred_title) {
            if (mb_strlen($title) > 0) {
                $this->preferred_title = $title;
            } else {
                $this->preferred_title = Spark::SiteTitle($selectedPath);
            }
        }

        if (!$this->description) {
            if (mb_strlen($description) > 0) {
                $this->description = $description;
            }
        }

        parent::applyTitleDescription();

    }

    protected function selectActiveMenu(): void
    {

        $main_menu = $this->menu_bar->getMenu();
        $main_menu->selectActive(array(MenuItemList::MATCH_FULL,MenuItemList::MATCH_PARTIAL));

    }

    public function __construct()
    {

        $this->auth = new UserAuthenticator();
        $this->loginURL = Spark::Get(Config::LOCAL) . "/account/login.php";

        parent::__construct();

        if ($this->context) {
            $this->client_name = (string)$this->context->getData()->get(SessionData::FULLNAME);
        }

        //template JSONFormDialog
        new JSONFormDialog();

        $factory = new BeanMenuFactory(new MenuItemsBean());
        $this->menu_bar = new MenuBar($factory->menu());
        $this->menu_bar->setName("StorePage");

        $ksc = new KeywordSearch();

        //just initialize the keyword form here. Search fields are initialized in ProductsListPage as form is submitted there
        $ksc->getForm()->getInput("keyword")->getRenderer()->input()?->setAttribute("placeholder", "Търси ...");
        $ksc->getForm()->getInput("keyword")->getRenderer()->input()?->setAttribute("autocomplete", "off");
        $ksc->getForm()->getInput("keyword")->setID("search-keyword");

        $ksc->getForm()->getRenderer()->setAttribute("method", "get");
        $ksc->getForm()->getRenderer()->setAttribute("action", new ProductListURL());
        $ksc->getForm()->getRenderer()->setAttribute("aria-label", "Product Search Form");

//        $ksc->getButton("search")->setComponentClass("");
        $ksc->getButton("search")->setContents("");
        $ksc->getButton("search")->setComponentClass("");
        $ksc->getButton("search")->setAttribute("aria-label", "Search Products");

        $ksc->getButton("clear")->setContents("");

        $ksc->getButton("clear")->setComponentClass("");
        //$ksc->getButton("clear")->setRenderEnabled(false);
        $ksc->setMethod(FormRenderer::METHOD_GET);
//        $show_search = new ColorButton();
//        $show_search->setAttribute("action", "show_search");
//        $show_search->setAttribute("onClick", "showSearch()");
//        $ksc->getButtons()->items()->append($show_search);

        $this->keyword_search = $ksc;

        $this->_header = new PageSection();
        $this->_header->addClassName("header");
        $this->_header->content()->setTagName("header");
        $this->_header->setAttribute("itemscope");
        $this->_header->setAttribute("itemtype", "https://schema.org/WPHeader");

        $this->_menu = new PageSection();
        $this->_menu->addClassName("menu");

        $this->_main = new PageSection();
        $this->_main->addClassName("main");

        $this->_cookies = new PageSection();
        $this->_cookies->addClassName("cookies");
        $this->_cookies->setTagName("aside");
        $this->_cookies->setAttribute("aria-label","Cookie Consent Notification");
        $this->_cookies->setAttribute("role", "dialog");
        $this->_cookies->setAttribute("tabindex", "0");

//        $this->_cookies->setAttribute("accepted", 0);

        $this->_footer = new PageSection();
        $this->_footer->addClassName("footer");

        $this->_page_footer = new PageSection();
        $this->_page_footer->addClassName("pageFooter");
        $this->_page_footer->content()->setTagName("footer");

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
            $this->head()->addJS(Spark::Get(StoreConfig::STORE_LOCAL)."/js/vouchers.js");
        }

        $this->home_action = new Action(tr("Products"));
        $this->home_action->setURL(new ProductListURL());
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
        $button_account->getURL()->fromString(Spark::Get(Config::LOCAL) . "/account/login.php");
        $button_account->setAttribute("title", tr("Account"));
        $button_account->setClassName("button account");
        $button_account->setContents($icon_contents);
        if ($this->context) {
            $button_account->getURL()->fromString(Spark::Get(Config::LOCAL) . "/account/");
            $button_account->addClassName("logged");
        }

        $container->items()->append($button_account);

        $button_cart = new Action();
        $button_cart->getURL()->fromString(Spark::Get(Config::LOCAL) . "/checkout/cart.php");
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

    protected function createLogo(string $href = "") : Component
    {
        if (!$href) {
            $href = Spark::Get(Config::LOCAL)."/home.php";
        }
        $link = new Action();
        $link->setTagName("a");
        $link->setAttribute("aria-label", "logo");
        $link->setAttribute("title", "logo");

        $link->setURL(new URL($href));
        $link->setComponentClass("logo");
        return $link;
    }

    protected function createVideo(string $src = "") : Video
    {
        if (!$src) {
            $src = Spark::Get(Config::LOCAL)."/images/header_logo";
        }
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
            $linkButton = Button::Action($result->get("item_title"), new URL(Spark::Get(Config::LOCAL)."/pages/index.php?id=".$result->get("dpID")));
            $linkButton->setComponentClass("item");
            $linkButton->setTitle($result->get("item_title"));
            $columnLinks->items()->append($linkButton);
        }


        $cfg = ConfigBean::Factory();
        $cfg->setSection("store_config");
        $phone = $cfg->get("phone_text");
        $phone = str_replace("\\r\\n", "<BR>", $phone);
        $email = $cfg->get("email_text");
        $email = str_replace("\\r\\n", "<BR>", $email);
        $location = $cfg->get("address_text");
        $location = str_replace("\\r\\n", "<BR>", $location);
        $working_hours = $cfg->get("working_hours_text");
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
            "terms"=>Spark::Get(Config::LOCAL)."/pages/index.php?class=terms",
            "contacts"=>Spark::Get(Config::LOCAL)."/contacts.php",
            "phone"=>"",
        );

        $cfg = new ConfigBean();
        $cfg->setSection("store_config");
        $facebook_href = $cfg->get("facebook_url");
        if ($facebook_href) {
            $items["facebook"] = $facebook_href;
        }
        $instagram_href = $cfg->get("instagram_url");
        if ($instagram_href) {
            $items["instagram"] = $instagram_href;
        }
        $youtube_href = $cfg->get("youtube_url");
        if ($youtube_href) {
            $items["youtube"] = $youtube_href;
        }
        $phone = $cfg->get("phone_orders");
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



    public static function ErrorPage(string $message, int $code=404) : void
    {
        http_response_code($code);
        $page = new StorePage();
        $page->addClassName("ErrorPage $code");
        $page->startRender();
        echo "<div class='description'>";
        echo $message;
        echo "</div>";
        $page->finishRender();
        exit;
    }




}
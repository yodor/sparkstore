<?php
include_once("pages/SparkPage.php");

include_once("utils/output/LDJsonScript.php");
include_once("utils/menu/BeanMenuFactory.php");

include_once("components/MenuBarComponent.php");
include_once("components/KeywordSearch.php");
include_once("components/ClosureComponent.php");


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

class SectionContainer extends Container {

    protected Container $space_left;
    protected Container $space_right;
    protected Container $content;

    public function __construct()
    {
        parent::__construct(false);
        $this->setComponentClass("section");

        $this->space_left = new Container(false);
        $this->space_left->setComponentClass("space left");
        $this->items()->append($this->space_left);

        $this->content = new Container(false);
        $this->content->setComponentClass("content");
        $this->items()->append($this->content);

        $this->space_right = new Container(false);
        $this->space_right->setComponentClass("space right");
        $this->items()->append($this->space_right);
    }

    public function spaceLeft() : Container
    {
        return $this->space_left;
    }

    public function content() : Container
    {
        return $this->content;
    }

    public function spaceRight() : Container
    {
        return $this->space_right;
    }

}

class StorePageBase extends SparkPage
{

    protected ?MenuBarComponent $menu_bar = NULL;

    protected ?KeywordSearch $keyword_search = NULL;

    public string $client_name = "";

    protected ?SectionContainer $_header = null;
    protected ?SectionContainer $_menu = null;
    protected ?SectionContainer $_main = null;
    protected ?SectionContainer $_footer = null;
    protected ?SectionContainer $_cookies = null;
    protected ?SectionContainer $_page_footer = null;

    public bool $vouchers_enabled = false;

    protected function headInitialize() : void
    {

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

            $this->head()->addScript($obj);
        }

        $config->setSection("store_config");
        $phone = $config->get("phone", "");

        $page_id = $config->get("tawkto_id", "");
        if ($page_id) {
            $this->head()->addScript(new TawktoScript($page_id));
        }

        $org_data = array("@context"     => "http://schema.org",
            "@type"        => "Organization",
            "name"         => SITE_TITLE,
            "url"          => SITE_URL,
            "logo"         => SITE_URL . "/images/logo_header.svg",
            "contactPoint" => array("@type"             => "ContactPoint",
                "telephone"         => $phone,
                "contactType"       => "sales",
                "areaServed"        => substr(DEFAULT_LANGUAGE_ISO3, 0, 2),
                "availableLanguage" => DEFAULT_LANGUAGE));


        $this->head()->addScript(new LDJsonScript($org_data));

        $www_data = array(
            "@context"=> "http://schema.org",
            "@type"=> "WebSite",
            "name"=> mb_strtoupper(SITE_TITLE). " - ОФИЦИАЛНА СТРАНИЦА",
            "url"=> SITE_URL,
            "potentialAction"=> array(
                "@type"=> "SearchAction",
                "target"=> SITE_URL."/products/list.php?filter=search&keyword={search_term_string}",
                "query-input"=> "required name=search_term_string"
            )
        );

        $this->head()->addScript(new LDJsonScript($www_data));


        $this->head()->addCSS(STORE_LOCAL . "/css/store.css");

        $this->head()->addJS(SPARK_LOCAL."/js/SparkCookies.js");
        $this->head()->addJS(STORE_LOCAL."/js/cookies.js");
        $this->head()->addJS(STORE_LOCAL."/js/menusticky.js");

        $this->head()->addOGTag("url", URL::Current()->fullURL()->toString());
        $this->head()->addOGTag("site_name", SITE_TITLE);
        $this->head()->addOGTag("type", "website");
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

        $this->headInitialize();

        $factory = new BeanMenuFactory(new MenuItemsBean());
        $this->menu_bar = new MenuBarComponent($factory->menu());
        $this->menu_bar->setName("StorePage");

        $ksc = new KeywordSearch();

        //just initialize the keyword form here. Search fields are initialized in ProductsListPage as form is posted there
        $ksc->getForm()->getInput("keyword")->getRenderer()->input()?->setAttribute("placeholder", "Търси ...");
        $ksc->getForm()->getRenderer()->setAttribute("method", "get");
        $ksc->getForm()->getRenderer()->setAttribute("action", LOCAL . "/products/list.php");
        $ksc->getButton("search")->setContents("");

        $ksc->getButton("clear")->setRenderEnabled(false);

//        $show_search = new ColorButton();
//        $show_search->setAttribute("action", "show_search");
//        $show_search->setAttribute("onClick", "showSearch()");
//        $ksc->getButtons()->items()->append($show_search);

        $this->keyword_search = $ksc;

        $this->_header = new SectionContainer();
        $this->_header->addClassName("header");
        $this->_header->setAttribute("itemscope", "");
        $this->_header->setAttribute("itemtype", "http://schema.org/WPHeader");

        $this->_menu = new SectionContainer();
        $this->_menu->addClassName("menu");

        $this->_main = new SectionContainer();
        $this->_main->addClassName("main");

        $this->_cookies = new SectionContainer();
        $this->_cookies->addClassName("cookies");
//        $this->_cookies->setAttribute("accepted", 0);

        $this->_footer = new SectionContainer();
        $this->_footer->addClassName("footer");

        $this->_page_footer = new SectionContainer();
        $this->_page_footer->addClassName("pageFooter");

        $this->_header->content()->items()->append(new ClosureComponent($this->renderHeader(...), false));

        $menu_groups = new ClosureComponent($this->renderMenuGroups(...), true);
        $menu_groups->setComponentClass("menu_groups");
        $this->menu_bar->items()->append($menu_groups);

        $this->_menu->content()->items()->append($this->menu_bar);

        $this->_cookies->content()->items()->append(new ClosureComponent($this->renderCookiesInfo(...), false));

        $this->_footer->content()->items()->append(new ClosureComponent($this->renderFooter(...), false));

        $this->_page_footer->content()->items()->append(new ClosureComponent($this->renderPageFooter(...), false));


        if ($this->vouchers_enabled) {
            $voucher_handler = new VoucherFormResponder();
            $this->head()->addJS(STORE_LOCAL."/js/vouchers.js");
        }

    }

    public function getMenuBar(): ?MenuBarComponent
    {
        return $this->menu_bar;
    }

    public function startRender()
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

    protected function renderMenuGroups(): void
    {

        echo "<div class='group search_pane'>";
        $this->keyword_search->render();
        echo "</div>";

        echo "<div class='group customer_pane'>";

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
        $button_account->render();

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
        $button_cart->render();

        echo "</div>";//group

    }

    protected function renderHeader()
    {

        $logo_href = LOCAL . "/home.php";
        echo "<a class='logo' href='{$logo_href}' title='logo'></a>";

        $cfg = new ConfigBean();
        $cfg->setSection("store_config");

//        echo "<div class='marquee'>";
            echo "<marquee>" . $cfg->get("marquee_text") . "</marquee>";
//        echo "</div>";

    }

    protected function renderPageFooter()
    {
        echo "<div class='columns'>";

        $cfg = new ConfigBean();
        $cfg->setSection("store_config");
        $phone = $cfg->get("phone_text", "");
        $phone = str_replace("\\r\\n", "<BR>", $phone);
        $email = $cfg->get("email_text", "");
        $email = str_replace("\\r\\n", "<BR>", $email);
        $location = $cfg->get("address_text", "");
        $location = str_replace("\\r\\n", "<BR>", $location);
        $working_hours = $cfg->get("working_hours_text", "");
        $working_hours = str_replace("\\r\\n", "<BR>", $working_hours);

        if ($this->vouchers_enabled) {
            echo "<div class='column voucher'>";
            echo "<a class='ColorButton' onClick='showVoucherForm()'>";
            echo tr("Купи ваучер");
            echo "</a>";
            echo "</div>";

        }

            echo "<div class='column page_links'>";
        $dp = new DynamicPagesBean();
        $query = $dp->query("item_title", "keywords", $dp->key());
        $query->select->where()->add("keywords", "'%footer_page%'", " LIKE ", " AND ");
        $query->select->where()->add("visible", 1);
            $num = $query->exec();
            while ($result = $query->nextResult()) {
                $href=LOCAL."/pages/index.php?id=".$result->get("dpID");
                echo "<a class='item' href='$href'>".$result->get("item_title")."</a>";
            }
            echo "</div>";

            echo "<div class='column address'>";
                echo "<div class='space'>";
                    echo "<div class='text phone'>";
                        echo "<div class='icon'></div>";
                        echo "<div class='value'>$phone</div>";
                    echo "</div>";

                    echo "<div class='text email'>";
                        echo "<div class='icon'></div>";
                        echo "<div class='value'>$email</div>";
                    echo "</div>";

                    echo "<div class='text location'>";
                        echo "<div class='icon'></div>";
                        echo "<div class='value'>$location</div>";
                    echo "</div>";

                    echo "<div class='text working_hours'>";
                        echo "<div class='icon'></div>";
                        echo "<div class='value'>$working_hours</div>";
                    echo "</div>";
                echo "</div>";
            echo "</div>";

        echo "</div>";
    }
    protected function renderFooter()
    {
        $cfg = new ConfigBean();
        $cfg->setSection("store_config");
        $facebook_href = $cfg->get("facebook_url", "/");
        $instagram_href = $cfg->get("instagram_url", "/");
        $youtube_href = $cfg->get("youtube_url", "/");
        $phone = $cfg->get("phone_orders", "");

        echo "<div class='social'>";
            if ($facebook_href) {
                echo "<a class='slot facebook' title='facebook' href='{$facebook_href}'></a>";
            }
            if ($instagram_href) {
                echo "<a class='slot instagram' title='instagram' href='{$instagram_href}'></a>";
            }
            if ($youtube_href) {
                echo "<a class='slot youtube' title='youtube' href='{$youtube_href}'></a>";
            }
            echo "<a class='slot terms' title='terms' href='".LOCAL."/pages/index.php?class=terms"."'></a>";

            echo "<a class='slot contacts' title='contacts' href='".LOCAL."/contacts.php'></a>";
            if ($phone) {
                echo "<a class='slot phone' title='phone' href='tel:$phone'></a>";
            }
        echo "</div>";


    }

    protected function renderCookiesInfo()
    {
        echo "<div class='info'>";

        echo "<a class='ColorButton' href='javascript:acceptCookies()'>";
        echo tr("Добре");
        echo "</a>";
        echo tr("Сайтът използва '<i>бисквитки</i>', за да подобри услугите. Продължавайки разглеждането му автоматично се съгласявате с тяхното използване.");

        echo "</div>";
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

    public function finishRender()
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


}

?>

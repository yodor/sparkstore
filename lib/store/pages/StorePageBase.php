<?php
include_once("pages/SparkPage.php");

include_once("utils/MainMenu.php");
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

class SectionContainer extends Container {

    protected $full = null;
    protected $space_left = null;
    protected $space_right = null;
    protected $content = null;

    public function __construct()
    {
        parent::__construct();
        $this->setComponentClass("section");

        $this->full = new Container();
        $this->full->setComponentClass("full");
        $this->append($this->full);

        $this->space_left = new Container();
        $this->space_left->setComponentClass("space left");
        $this->full->append($this->space_left);

        $this->content = new Container();
        $this->content->setComponentClass("content");
        $this->full->append($this->content);

        $this->space_right = new Container();
        $this->space_right->setComponentClass("space right");
        $this->full->append($this->space_right);
    }

    public function full() : Container
    {
        return $this->full;
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

    protected $menu_bar = NULL;

    /**
     * @var KeywordSearch|null
     */
    protected $keyword_search = NULL;

    public $client_name = "";

    protected $_header = null;
    protected $_menu = null;
    protected $_main = null;
    protected $_footer = null;
    protected $_cookies = null;

    public function __construct()
    {

        $this->auth = new UserAuthenticator();
        $this->loginURL = LOCAL . "/account/login.php";

        parent::__construct();

        $this->authorize();

        if ($this->context) {

            $this->client_name = $this->context->getData()->get(SessionData::FULLNAME);

        }

        $menu = new MainMenu();

        $menu->setBean(new MenuItemsBean());
        $menu->construct();

        $this->menu_bar = new MenuBarComponent($menu);

        $this->menu_bar->setName("StorePage");
        $this->menu_bar->toggle_first = TRUE;

        $ksc = new KeywordSearch();
        //just initialize the keyword form here. Search fields are initialized in ProductsListPage as form is posted there
        $ksc->getForm()->getInput("keyword")->getRenderer()->setInputAttribute("placeholder", "Търси ...");
        $ksc->getForm()->getRenderer()->setAttribute("method", "get");
        $ksc->getForm()->getRenderer()->setAttribute("action", LOCAL . "/products/list.php");

        $ksc->getButton("search")->setContents("");

        $this->keyword_search = $ksc;

        $this->addCSS(STORE_LOCAL . "/css/store.css");
        //$this->addCSS("//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css");

        //$this->addJS("//code.jquery.com/ui/1.11.4/jquery-ui.js");
        //$this->addJS(SPARK_LOCAL . "/js/URI.js");



        $this->addJS(STORE_LOCAL."/js/StoreCookies.js");


        $pc = new ProductCategoriesBean();
        $qry = $pc->query("category_name");
        $num = $qry->exec();
        $kewords = array();
        while ($result = $qry->next()) {
            $keywords[] = mb_strtolower($result["category_name"]);
        }

        $this->keywords = implode(", ", $keywords);

        $this->addOGTag("title", "%title%");
        $this->addOGTag("description", "%meta_description%");
        $this->addOGTag("url", fullURL($this->getPageURL()));
        $this->addOGTag("site_name", SITE_TITLE);
        $this->addOGTag("type", "website");

        $this->_header = new SectionContainer();
        $this->_header->addClassName("header");

        $this->_menu = new SectionContainer();
        $this->_menu->addClassName("menu");

        $this->_main = new SectionContainer();
        $this->_main->addClassName("main");

        $this->_cookies = new SectionContainer();
        $this->_cookies->addClassName("cookies");
//        $this->_cookies->setAttribute("accepted", 0);

        $this->_footer = new SectionContainer();
        $this->_footer->addClassName("footer");

        $header_callback = function(ClosureComponent $parent) {
            $this->renderHeader();
        };
        $this->_header->content()->append(new ClosureComponent($header_callback, false));

        $menu_callback = function(ClosureComponent $parent) {
            $this->renderMenu();
        };
        $this->_menu->content()->append(new ClosureComponent($menu_callback, false));

        $cookies_callback = function(ClosureComponent $parent) {
            $this->renderCookiesInfo();
        };
        $this->_cookies->content()->append(new ClosureComponent($cookies_callback, false));

        $footer_callback = function(ClosureComponent $parent) {
            $this->renderFooter();
        };
        $this->_footer->content()->append(new ClosureComponent($footer_callback, false));


    }

    protected function headStart()
    {
        parent::headStart();

        $cfg = new ConfigBean();
        $cfg->setSection("store_config");
        $phone = $cfg->get("phone", "");

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

        $this->renderLDJSON($org_data);


        $this->renderSearchLD();

    }

    protected function renderSearchLD()
    {
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

        $this->renderLDJSON($www_data);
    }

    public function renderLDJSON(array $data)
    {
        echo "<script type='application/ld+json'>";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        echo "</script>";
    }

    public function getMenuBar()
    {
        return $this->menu_bar;
    }

    public function startRender()
    {
        parent::startRender();

        echo "\n<!-- startRender StorePage-->\n";

        $this->selectActiveMenu();

        $this->_header->render();
        $this->_menu->render();

        $this->_main->startRender();

        $this->_main->spaceLeft()->render();

        $this->_main->content()->startRender();

    }

    protected function renderMenu()
    {
        echo "<div class='menuwrap'>";

        $this->menu_bar->render();

        echo "<div class='group'>";
        echo "<div class='search_pane'>";
        $this->keyword_search->render();
        echo "<div class='clear'></div>";
        echo "</div>";

        echo "<div class='customer_pane'>";

        $icon_contents = "<span class='icon'></span>";
        $button_account = new Action();
        $button_account->getURLBuilder()->buildFrom(LOCAL . "/account/login.php");
        $button_account->setAttribute("title", tr("Account"));
        $button_account->setClassName("button account");
        $button_account->setContents($icon_contents);
        if ($this->context) {
            $button_account->getURLBuilder()->buildFrom(LOCAL . "/account/");
            $button_account->addClassName("logged");
        }
        $button_account->render();

        $button_cart = new Action();
        $button_cart->getURLBuilder()->buildFrom(LOCAL . "/checkout/cart.php");
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

        echo "</div>"; //customer_pane

        echo "</div>";//group

        echo "</div>";//menuwrap
    }

    protected function renderHeader()
    {

        $logo_href = LOCAL . "/home.php";
        echo "<a class='logo' href='{$logo_href}' title='logo'></a>";

        $cfg = new ConfigBean();
        $cfg->setSection("store_config");

        echo "<div class='marquee'>";
            echo "<marquee>" . $cfg->get("marquee_text") . "</marquee>";
        echo "</div>";

    }

    protected function renderFooter()
    {
        $cfg = new ConfigBean();
        $cfg->setSection("store_config");
        $facebook_href = $cfg->get("facebook_url", "/");
        $instagram_href = $cfg->get("instagram_url", "/");
        $youtube_href = $cfg->get("youtube_url", "/");
        $phone = $cfg->get("phone", "");

        echo "<div class='social'>";
            echo "<a class='slot facebook' title='facebook' href='{$facebook_href}'></a>";
            echo "<a class='slot instagram' title='instagram' href='{$instagram_href}'></a>";
            echo "<a class='slot youtube' title='youtube' href='{$youtube_href}'></a>";
            echo "<a class='slot contacts' title='contacts' href='".LOCAL."/contacts.php'></a>";
            echo "<a class='slot terms' title='terms' href='".LOCAL."/terms_usage.php"."'></a>";
            echo "<a class='slot phone' title='phone' href='tel:$phone'></a>";
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

        $main_menu = $this->menu_bar->getMainMenu();
        $main_menu->selectActive(array(MainMenu::MATCH_FULL,MainMenu::MATCH_PARTIAL));

    }

    protected function constructTitle()
    {
        if (strlen($this->getTitle()) > 0) return;

        $main_menu = $this->menu_bar->getMainMenu();

        $this->setTitle(constructSiteTitle($main_menu->getSelectedPath()));
    }

    public function finishRender()
    {
        $this->_main->content()->finishRender();
        $this->_main->spaceRight()->render();
        $this->_main->finishRender();

        $this->_cookies->render();
        $this->_footer->render();


        echo "\n";
        echo "\n<!-- finishRender StorePage-->\n";

        $this->constructTitle();
?>
        <script type="text/javascript">

            //to check when element get's position sticky
            var observer = new IntersectionObserver(function(entries) {
                // no intersection
                if (entries[0].intersectionRatio === 0)
                    document.querySelector(".section.menu").classList.add("sticky");
                // fully intersects
                else if (entries[0].intersectionRatio === 1)
                    document.querySelector(".section.menu").classList.remove("sticky");
            }, {
                threshold: [0, 1]
            });


            observer.observe(document.querySelector(".section.header"));


            let storeCookies = new StoreCookies();

            function acceptCookies()
            {
                storeCookies.accept();
                updateCookies();
            }

            function updateCookies()
            {
                let isAccepted = storeCookies.isAccepted();

                $(".section.cookies").attr("checked", 1);

                if (isAccepted) {
                    $(".section.cookies").attr("accepted", 1);
                }
                else {
                    $(".section.cookies").attr("accepted", 0);
                }
            }

            onPageLoad(function(){

                updateCookies();


            });

        </script>
<?php
        parent::finishRender();

    }

}

?>

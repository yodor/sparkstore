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
include_once("beans/DynamicPagesBean.php");
include_once("store/responders/json/VoucherFormResponder.php");


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
    protected $_page_footer = null;

    public bool $vouchers_enabled = false;

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

//        $input = DataInputFactory::Create(DataInputFactory::HIDDEN, KeywordSearch::SUBMIT_KEY, "",0);
//        $input->setValue(KeywordSearch::ACTION_SEARCH);
//        $input->setEditable(false);
//        $ksc->getForm()->addInput($input);
//
//        $ksc->getButtons()->clear();
        $ksc->getButton("search")->setContents("");

        $show_search = new ColorButton();
        $show_search->setAttribute("action", "show_search");
        $show_search->setAttribute("onClick", "showSearch()");
        $ksc->getButtons()->append($show_search);


        $this->keyword_search = $ksc;

        $this->addCSS(STORE_LOCAL . "/css/store.css");
        //$this->addCSS("//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css");

        //$this->addJS("//code.jquery.com/ui/1.11.4/jquery-ui.js");
        //$this->addJS(SPARK_LOCAL . "/js/URI.js");



        $this->addJS(STORE_LOCAL."/js/StoreCookies.js");



        $pc = new ProductCategoriesBean();
        $qry = $pc->query("category_name");
        $num = $qry->exec();
        $keywords = array();
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

        $this->_page_footer = new SectionContainer();
        $this->_page_footer->addClassName("pageFooter");



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

        $pageFooter_callback = function(ClosureComponent $parent) {
            $this->renderPageFooter();
        };
        $this->_page_footer->content()->append(new ClosureComponent($pageFooter_callback, false));


        if ($this->vouchers_enabled) {
            $voucher_handler = new VoucherFormResponder();
        }

    }

    public function getAuthContext() : ?AuthContext
    {
        return $this->context;
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

        $cfg = new ConfigBean();
        $cfg->setSection("store_config");
        $page_id = $cfg->get("facebook_page_id", "");
        if ($page_id) {
            echo "\n<!-- Facebook Chat Plugin start -->\n";
            $this->renderFBChatPlugin($page_id);
            echo "\n<!-- Facebook Chat Plugin end -->\n";
        }

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
        $query = $dp->query("item_title", "keywords", "dpID");
        $query->select->where()->add("keywords", "'%footer_page%'", " LIKE ");
        $query->select->where()->add("visible", 1);
            $num = $query->exec();
            while ($result = $query->nextResult()) {
                $href=LOCAL."/terms_usage.php?dpID=".$result->get("dpID");
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
            //echo "<a class='slot terms' title='terms' href='".LOCAL."/terms_usage.php"."'></a>";
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

        $this->_page_footer->render();

        $this->_cookies->render();
        $this->_footer->render();


        echo "\n";
        echo "\n<!-- finishRender StorePage-->\n";

        $this->constructTitle();
?>
        <script type="text/javascript">

            //to check when element get's position sticky
            var observer = new IntersectionObserver(function(entries) {

                if (entries[0].intersectionRatio === 0) {
                    document.querySelector(".section.menu").classList.add("sticky");

                }
                else if (entries[0].intersectionRatio === 1) {
                    document.querySelector(".section.menu").classList.remove("sticky");
                }

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

            function showVoucherForm()
            {
                let voucher_dialog = new JSONFormDialog();
                voucher_dialog.setResponder("VoucherFormResponder");
                voucher_dialog.caption="Kупи Ваучер";

                let dialog = new MessageDialog()
                dialog.initialize();
                dialog.text = "Ще получите Вашият ваучер по куриер";

                dialog.buttonAction = function (action) {

                    if (action == "confirm") {
                        dialog.remove();
                        voucher_dialog.show();
                    }
                    else if (action == "cancel") {
                        dialog.remove();
                    }
                }

                dialog.show();
            }
            function showSearch()
            {
                let form = document.querySelector(".section.menu .content .search_pane FORM");
                let dcomp = $(".KeywordSearch .InputComponent").css("display");
                let dfield = $(".KeywordSearch .InputComponent .InputField").css("display");
                if (dcomp == "block" && dfield == "inline-block") {
                    form.submit();
                    return;
                }

                if (form.classList.contains("fixed")) {
                    form.classList.remove("fixed");
                }
                else {
                    form.classList.add("fixed");
                }
            }
        </script>
<?php
        parent::finishRender();

    }

    public function renderFBChatPlugin(string $page_id)
    {
        ?>
        <!-- Messenger Chat Plugin Code -->
        <div id="fb-root"></div>

        <!-- Your Chat Plugin code -->
        <div id="fb-customer-chat" class="fb-customerchat">
        </div>

        <script>
            var chatbox = document.getElementById('fb-customer-chat');
            chatbox.setAttribute("page_id", "<?php echo $page_id;?>");
            chatbox.setAttribute("attribution", "biz_inbox");
        </script>

        <!-- Your SDK code -->
        <script>
            window.fbAsyncInit = function() {
                FB.init({
                    xfbml            : true,
                    version          : 'v15.0'
                });
            };

            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>
        <?php
    }
}

?>
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
include_once("store/utils/url/StoreMenuLinks.php");
include_once("store/utils/IMenuAddresses.php");
include_once("store/utils/url/ProductSearchURL.php");
include_once("store/beans/SectionsBean.php");

include_once("auth/UserAuthenticator.php");

include_once("store/utils/CurrencyConverter.php");

include_once("store/beans/ProductCategoriesBean.php");

include_once("store/utils/cart/Cart.php");
include_once("beans/DynamicPagesBean.php");
include_once("utils/url/DynamicPageURL.php");
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
include_once("store/components/frame/StoreFrame.php");


class StorePageBase extends SparkPage
{

    protected ?Action $home_action = null;

    protected ?MenuBar $menu_bar = NULL;

    protected ?KeywordSearch $keyword_search = NULL;

    public string $client_name = "";

    /**
     * The logged client of this request (UserAuthenticator) - null for a visitor
     * @var AuthContext|null
     */
    protected ?AuthContext $client = null;

    /**
     * The logged client: the context of the module when it authorizes clients (account, checkout), else
     * the UserAuthenticator session - no login is required here
     * @return AuthContext|null
     */
    public static function ClientContext() : ?AuthContext
    {
        if (class_exists("Module", false) && Module::HasActive() && Module::Active()->getAuthenticator() instanceof UserAuthenticator) {
            return Module::Active()->getAuthContext();
        }
        return (new UserAuthenticator())->authorize();
    }

    /**
     * The stylesheet of the store font (Montserrat) - a site sets its own, or a self hosted one
     * @var string
     */
    public static string $fontCSS = "https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap";

    /**
     * The sections of the frame (StoreFrame - null when a site disabled one); the main section holds the content
     */
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

        //the store font - a link of its own (not an @import inside store.css: loaded in parallel, not after it)
        $this->head()->addCSS(StorePageBase::$fontCSS);
        $this->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/store.css");
        //the menu bar on phones and tablets
        $this->head()->addCSS(Spark::Get(Config::SPARK_LOCAL) . "/css/mobile/MenuBar.css", false, "(max-width: 1024px)");
        //the css of the site for the frame (StoreFrame::Stylesheet - the site init)
        foreach (StoreFrame::Stylesheets() as $href) {
            $this->head()->addCSS($href);
        }



        //update consent
        $this->head()->addJS(Spark::Get(StoreConfig::STORE_LOCAL)."/js/cookies.js");
        $this->head()->addJS(Spark::Get(StoreConfig::STORE_LOCAL)."/js/menusticky.js");

        $config = ConfigBean::Factory();
        $config->setSection("marketing_config");

        $facebookID_pixel = $config->get("facebookID_pixel");
        if ($facebookID_pixel) {
            $this->head()->addScript(new FBPixel($facebookID_pixel));
        }


        //default consent fired from sparkGTM
//        $default_consent = new GTMConsentCommand();
//        $this->head()->addScript($default_consent->script());

        $googleID_analytics = $config->get("googleID_analytics");
        $googleID_ads = $config->get("googleID_ads");

        $gtag = null;
        if ($googleID_ads) {
            $gtag = new GTAG($googleID_ads);
        }
        else if ($googleID_analytics) {
            $gtag = new GTAG($googleID_analytics);
        }

        if ($gtag instanceof GTAG) {

            $this->head()->addScript($gtag);

            if ($googleID_ads) {
                $cmd = new GTMCommand();
                $cmd->setCommand(GTMCommand::COMMAND_CONFIG);
                $cmd->setType($googleID_ads);
                $this->head()->addScript($cmd);
            }

            if ($googleID_analytics) {
                $cmd = new GTMCommand();
                $cmd->setCommand(GTMCommand::COMMAND_CONFIG);
                $cmd->setType($googleID_analytics);
                $this->head()->addScript($cmd);
            }

            //any page conversion
            $conversionID = $config->get(GTMConvParam::VIEW_ANY_PAGE->value);
            if ($conversionID) {
                $cmd = new GTMConversionCommand($conversionID);
                $this->head()->addScript($cmd);
            }
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
        $website->set("name", Spark::Get(Config::SITE_TITLE). " - " . "Official Web Site");
        $website->set("url", Spark::Get(Config::SITE_URL));

        $potentialAction = new LinkedData("SearchAction");

        $entryPoint = new LinkedData("EntryPoint");

        $entryURL = new ProductListURL();
        $entryURL->add(new URLParameter("filter","search"));
        $entryURL->add(new LiteralURLParameter("keyword", "{search_term_string}"));

        $entryPoint->set("urlTemplate", $entryURL->fullURL());
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

        //the font files of the store font
        $link = new Link();
        $link->setRelation("preconnect");
        $link->setHref("https://fonts.gstatic.com");
        $link->setAttribute("crossorigin", "");
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

        //the store default: 'Fallback Meta Description' of the store config (SparkStoreConfigForm)
        if (!$this->description) {
            $config = ConfigBean::Factory();
            $config->setSection("store_config");
            $this->description = (string)$config->get("meta_description");
        }

        //else the SEO settings of the site (core)
        parent::applyTitleDescription();

    }

    /**
     * The addresses of the page for the selection of the main menu, the preferred first (MenuItemList::selectActive)
     * @return array of URL|string
     */
    protected function menuAddresses() : array
    {
        $addresses = array(URL::Requested());
        //the addresses of the content's place (a product: its categories)
        if ($this->pageContent instanceof IMenuAddresses) {
            foreach ($this->pageContent->menuAddresses() as $address) $addresses[] = $address;
        }
        return $addresses;
    }

    protected function selectActiveMenu(): void
    {
        $this->menu_bar->getMenu()->selectActive(...$this->menuAddresses());
    }

    public function __construct()
    {
        parent::__construct();

        //the logged client - shown in the header (account button, name); the pages require no login,
        //the account and checkout modules authorize their paths
        $this->client = StorePageBase::ClientContext();
        if ($this->client) {
            $this->client_name = (string)$this->client->getData()->get(AuthContext::FULLNAME);
        }

        //template JSONFormDialog
        new JSONFormDialog();

        $factory = new BeanMenuFactory(new MenuItemsBean());
        $this->menu_bar = new MenuBar($factory->menu());
        //the current addresses of the old products links
        StoreMenuLinks::Resolve($this->menu_bar->getMenu());
        $this->menu_bar->setName("StorePage");

        $ksc = new KeywordSearch();

        //just initialize the keyword form here. Search fields are initialized in ProductsListPage as form is submitted there
        $ksc->getForm()->getInput("keyword")->getRenderer()->input()?->setAttribute("placeholder", "Търси ...");
        $ksc->getForm()->getInput("keyword")->getRenderer()->input()?->setAttribute("autocomplete", "off");
        $ksc->getForm()->getInput("keyword")->getRenderer()->input()?->setAttribute("min-chars", "3");
        $ksc->getForm()->getInput("keyword")->getRenderer()->input()?->setAttribute("id", "search-keyword");

        $ksc->setMethod(FormRenderer::METHOD_GET);
        //the search of the products module (/products/search/ - not indexed)
        $ksc->setAction(new ProductSearchURL());
        $ksc->setAttribute("aria-label", "Product Search Form");

        $ksc->getButton("search")->setContents("");
        $ksc->getButton("search")->setComponentClass("");
        $ksc->getButton("search")->setAttribute("aria-label", "Search Products");

        $ksc->getButton("clear")->setContents("");
        $ksc->getButton("clear")->setComponentClass("");
        $ksc->getButton("clear")->setAttribute("aria-label", "Clear Search");

        $this->keyword_search = $ksc;

        $this->_main = new PageSection();
        $this->_main->addClassName("main");

        //the sections of the frame (StoreFrame); a site StorePage that still overrides the old hooks builds them so
        $this->_header = $this->frameSection(StoreFrame::HEADER, "initHeaderSection", function (StoreSection $section) {
            $this->initHeaderSection($section->content());
        });

        $this->_menu = $this->frameSection(StoreFrame::MENU, array("initMenuGroups", "createSearchMenuGroup", "createCustomerMenuGroup"), function (StoreSection $section) {
            $section->content()->items()->append($this->menu_bar);
            $menu_groups = new Container(false);
            $menu_groups->setComponentClass("menu_groups");
            $this->initMenuGroups($menu_groups);
            $this->menu_bar->items()->append($menu_groups);
        });

        //after the content of every page
        $this->_page_footer = $this->frameSection(StoreFrame::PAGE_FOOTER, "initPageFooterSection", function (StoreSection $section) {
            $this->initPageFooterSection($section->content());
        });

        $this->_cookies = $this->frameSection(StoreFrame::COOKIES, "createCookiesInfoPanel", function (StoreSection $section) {
            $section->content()->items()->append($this->createCookiesInfoPanel());
        });

        //the fixed footer
        $this->_footer = $this->frameSection(StoreFrame::FOOTER, "initFooterSection", function (StoreSection $section) {
            $this->initFooterSection($section->content());
        });

        if ($this->vouchers_enabled) {
            $voucher_handler = new VoucherFormResponder();
            $this->head()->addJS(Spark::Get(StoreConfig::STORE_LOCAL)."/js/vouchers.js");
        }

        $home = StoreFrame::GetHomeAction();
        if (is_array($home)) {
            $this->home_action = new Action($home[0]);
            $this->home_action->setURL(new URL($home[1]));
        }
        else {
            $this->home_action = new Action(tr("Products"));
            $this->home_action->setURL(new ProductListURL());
        }
    }

    /**
     * The section '$name' of the frame, built - null when the site disabled it. A site page class that overrides
     * one of '$hooks' (the hooks before the sections - initHeaderSection ...) builds it with '$legacy'.
     * @param string $name
     * @param string|array $hooks
     * @param Closure $legacy
     * @return StoreSection|null
     * @throws Exception
     */
    protected function frameSection(string $name, string|array $hooks, Closure $legacy) : ?StoreSection
    {
        if (!StoreFrame::IsEnabled($name)) return null;
        $section = StoreFrame::Section($name);
        foreach ((array)$hooks as $hook) {
            if ((new ReflectionMethod($this, $hook))->getDeclaringClass()->getName() !== StorePageBase::class) {
                $legacy($section);
                return $section;
            }
        }
        $section->initialize($this);
        return $section;
    }

    /**
     * The logged client of this request - null for a visitor
     * @return AuthContext|null
     */
    public function getClient() : ?AuthContext
    {
        return $this->client;
    }

    /**
     * The content of the module path in the main section: its columns (the items of a Container content) or
     * its component
     * @param TemplateContent $content
     * @return void
     */
    public function setContent(TemplateContent $content) : void
    {
        $this->pageContent = $content;

        $config = $content->config();
        if ($config->title) $this->setTitle($config->title);

        $component = $content->component();
        if ($component instanceof Container) {
            foreach ($component->items()->toArray() as $item) {
                $this->items()->append($item);
            }
            return;
        }
        $this->items()->append($component);
    }

    /**
     * The keyword search of the header - a content can search its own items with it (setAction, setColumns)
     * @return KeywordSearch|null
     */
    public function getKeywordSearch(): ?KeywordSearch
    {
        return $this->keyword_search;
    }

    /**
     * The 'products list' action of the store (the first item of the product breadcrumbs)
     * @return Action|null
     */
    public function getHomeAction(): ?Action
    {
        return $this->home_action;
    }

    /**
     * The main section of the frame - where the content goes
     * @return PageSection|null
     */
    public function getMainSection(): ?PageSection
    {
        return $this->_main;
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

        $this->_header?->render();
        $this->_menu?->render();


        $this->_main->startRender();
        $this->_main->spaceLeft()->render();
        $this->_main->content()->startRender();

    }

    public function finishRender(): void
    {
        $this->_main->content()->finishRender();
        $this->_main->spaceRight()->render();
        $this->_main->finishRender();

        $this->_page_footer?->render();

        $this->_cookies?->render();
        $this->_footer?->render();


        echo "\n";
        echo "\n<!-- finishRender StorePage-->\n";

        parent::finishRender();

    }

    /*
     * The hooks before the sections (StoreFrame) - a site StorePage that overrides one of them builds that section
     * itself (frameSection). Their defaults are the sections' own parts. To go when no site overrides them.
     */

    protected function createSearchMenuGroup() : ?Container
    {
        return StoreMenu::SearchGroup($this->keyword_search);
    }

    protected function createCustomerMenuGroup() : ?Container
    {
        return StoreMenu::CustomerGroup($this->client instanceof AuthContext);
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
        return StoreHeader::Logo($href);
    }

    protected function createVideo(string $src = "") : Video
    {
        return StoreHeader::Video($src);
    }

    protected function initHeaderSection(Container $container) : void
    {
        StoreFrame::Header()->fill($container, $this);
    }

    protected function initPageFooterSection(Container $container): void
    {
        StoreFrame::PageFooter()->fill($container, $this);
    }

    protected function initFooterSection(Container $container) : void
    {
        StoreFrame::Footer()->fill($container, $this);
    }

    protected function createCookiesInfoPanel() : Container
    {
        return StoreCookies::InfoPanel();
    }
}

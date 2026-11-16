<?php
include_once("class/pages/StorePage.php");
include_once("templates/Template.php");
include_once("templates/TemplateConfig.php");
include_once("utils/menu/MenuItemList.php");
include_once("utils/menu/MenuItem.php");
include_once("store/templates/content/AccountContent.php");

/**
 * Page of the account module: the storefront look (the site StorePage) with the content of the module path.
 *
 * The path handling is the one of SparkTemplatePage - a StorePage can not extend it. It is to be shared when
 * the storefront pages move to the module engine (step 8 of the work plan).
 *
 * Layout: the 'columns' container - the account menu column (logged in clients) and the content columns.
 * AccountContent gives its columns, the component of any other content (error) is put in one column.
 * The body gets the path as a css class: 'orders/details' → 'orders_details'.
 */
class AccountTemplatePage extends StorePage implements IObserver
{
    protected string $path = "home";

    protected Container $columns;

    protected static ?MenuItemList $Menu = null;

    public function __construct()
    {
        parent::__construct();

        //the module authorized already - the page uses the same client context
        $this->loginURL = Module::PathURL("login")->toString();

        SparkEventManager::register(TemplateEvent::class, $this);
        SparkEventManager::register(TemplateConfigEvent::class, $this);

        $this->addParameterName("path");
        //the module folder itself (/account/) is the 'home' path
        if (isset($_GET["path"]) && trim((string)$_GET["path"], "/") !== "") {
            $this->path = Template::FormatPath($_GET["path"], "/", true);
        }

        $this->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/account.css");
        $this->body()->addClassName("account");

        $this->columns = new Container(false);
        $this->columns->setComponentClass("columns");
        $this->items()->append($this->columns);

        if (Module::Active()->getAuthContext()) {
            $this->columns->items()->append($this->createMenuColumn());
        }
    }

    /**
     * Menu items of the module menu.php files (store, site): name and module path
     * @return MenuItemList
     */
    public static function Menu() : MenuItemList
    {
        if (is_null(AccountTemplatePage::$Menu)) AccountTemplatePage::$Menu = new MenuItemList();
        return AccountTemplatePage::$Menu;
    }

    protected function createMenuColumn() : Container
    {
        SparkLoader::Factory(Module::Active()->getPreifx())->include("menu", true);

        $column = new Container(false);
        $column->setComponentClass("column account_menu");

        $links = new Container(false);
        $links->setComponentClass("menu_links");
        $column->items()->append($links);

        //top level section of the path marks its item
        $section = explode("/", Template::FormatPath($this->path, "/", false))[0] ?? "";

        foreach (AccountTemplatePage::Menu()->toArray() as $item) {
            if (!($item instanceof MenuItem)) continue;
            $link = new Action();
            $link->setURL(Module::PathURL($item->getHref()));
            $link->setClassName("item");
            $link->setText($item->getName());
            if (strcmp($item->getHref(), $section) === 0) {
                $link->addClassName("selected");
                $link->setAttribute("aria-current", "page");
            }
            $links->items()->append($link);
        }

        //under the links - a block of its own
        $logout = new Container(false);
        $logout->setComponentClass("logout");
        $button = Button::LocationButton(tr("Изход"), Module::PathURL("logout"));
        $button->setAttribute("action", "logout");
        $logout->items()->append($button);
        $column->items()->append($logout);

        return $column;
    }

    /**
     * Load the configuration of the path - Template::PathConfig (SparkTemplatePage::initialize)
     * @return void
     */
    public function initialize() : void
    {
        try {
            Template::PathConfig($this->path);
            $this->body->setAttribute("path", $this->path);
            $this->body->addClassName(Template::FormatPath($this->path, "_", false));
        }
        catch (Exception $e) {
            Debug::ErrorLog("PathConfig failed: " . $e->getMessage());
            $message = $e->getMessage();
            if (Debug::IsEnabled()) $message .= " - " . $e->getTraceAsString();
            Template::ErrorConfig("Error:{$this->path}", $message);
        }
    }

    /**
     * Put the content columns in the page
     * @param TemplateContent $content
     * @return void
     */
    public function update(TemplateContent $content) : void
    {
        $config = $content->config();
        if ($config->title) $this->setTitle($config->title);

        if ($content instanceof AccountContent) {
            foreach ($content->component()->items()->toArray() as $column) {
                $this->columns->items()->append($column);
            }
            return;
        }

        if ($config->description) {
            $content->component()->setCaption($config->description);
        }
        $this->columns->items()->append(AccountContent::Column($config->title, $content->component()));
    }

    /**
     * Template::LoadContent() when the path config is ready, update() when the content processed its input
     * (SparkTemplatePage::onEvent)
     * @param SparkEvent $event
     * @return void
     * @throws Exception
     */
    public function onEvent(SparkEvent $event) : void
    {
        if ($event->isEvent(TemplateConfigEvent::UPDATE)) {
            Template::LoadContent();
        }
        else if ($event->isEvent(TemplateEvent::CONTENT_INPUT_PROCESSED)) {
            $content = $event->getSource();
            if (!($content instanceof TemplateContent)) throw new Exception("Incorrect event source - expecting TemplateContent");
            $this->update($content);
        }
    }

    public function currentURL() : URL
    {
        return Module::PathURL("", parent::currentURL());
    }
}

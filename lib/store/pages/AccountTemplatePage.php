<?php
include_once("class/pages/StorePage.php");
include_once("utils/menu/MenuItemList.php");
include_once("utils/menu/MenuItem.php");
include_once("store/templates/content/AccountContent.php");

/**
 * Page of the account module (the site StorePage frame with the content of the module path).
 *
 * Layout: the 'columns' container - the account menu column (logged in clients) and the content columns.
 * AccountContent gives its columns, the component of any other content (error) is put in one column.
 */
class AccountTemplatePage extends StorePage
{
    protected Container $columns;

    protected static ?MenuItemList $Menu = null;

    public function __construct()
    {
        parent::__construct();

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
        $section = explode("/", Template::FormatPath(Module::Active()->getPath(), "/", false))[0] ?? "";

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
     * Put the content columns in the page
     * @param TemplateContent $content
     * @return void
     */
    public function setContent(TemplateContent $content) : void
    {
        $this->pageContent = $content;
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


}

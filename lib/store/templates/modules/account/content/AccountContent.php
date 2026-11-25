<?php
include_once("templates/TemplateContent.php");
include_once("utils/menu/MenuItemList.php");
include_once("utils/menu/MenuItem.php");
include_once("components/Action.php");
include_once("components/Button.php");

/**
 * Content of the account module - the layout of the account pages in the store frame: the 'columns' container
 * with the account menu column (a logged in client) and the columns of the content (addColumn, Column()).
 * The module look (account.css) is set by the account init.php.
 */
abstract class AccountContent extends TemplateContent
{
    /**
     * The account menu (the menu.php files of the module: store, site)
     * @var MenuItemList|null
     */
    protected static ?MenuItemList $Menu = null;

    protected Container $columns;

    public function __construct()
    {
        parent::__construct();
        $this->cmp = new Container(false);

        $this->columns = new Container(false);
        $this->columns->setComponentClass("columns");
        $this->cmp->items()->append($this->columns);

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
        if (is_null(AccountContent::$Menu)) AccountContent::$Menu = new MenuItemList();
        return AccountContent::$Menu;
    }

    /**
     * The account menu column: the links (the section of the path selected) and the logout button
     * @return Container
     */
    protected function createMenuColumn() : Container
    {
        SparkLoader::Factory(Module::Active()->getPrefix())->include("menu", true);

        $column = new Container(false);
        $column->setComponentClass("column account_menu");

        $links = new Container(false);
        $links->setComponentClass("menu_links");
        $column->items()->append($links);

        //top level section of the path marks its item
        $section = explode("/", Module::FormatPath(Module::Active()->getPath(), "/", false))[0] ?? "";

        foreach (AccountContent::Menu()->toArray() as $item) {
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
        $button = Button::LocationButton(tr("Logout"), Module::PathURL("logout"));
        $button->setAttribute("action", "logout");
        $logout->items()->append($button);
        $column->items()->append($logout);

        return $column;
    }

    /**
     * One column of the account page: the caption and the panel with '$component'
     * @param string $caption Text (escaped)
     * @param Component $component
     * @param string $className Additional css class of the column
     * @return Container
     */
    public static function Column(string $caption, Component $component, string $className = "") : Container
    {
        $column = new Container(false);
        $column->setComponentClass("column");
        if ($className !== "") $column->addClassName($className);

        if ($caption !== "") {
            $title = new Component(false);
            $title->setTagName("h1");
            $title->setComponentClass("Caption");
            $title->setText($caption);
            $column->items()->append($title);
        }

        $panel = new Container(false);
        $panel->setComponentClass("panel");
        $panel->items()->append($component);
        $column->items()->append($panel);

        return $column;
    }

    /**
     * Add a column to the content
     * @param Container $column
     * @return void
     */
    protected function addColumn(Container $column) : void
    {
        $this->columns->items()->append($column);
    }

    public function component() : Container
    {
        return $this->cmp;
    }

    /**
     * Redirect to the module path '$path' (after processing) and stop
     * @param string $path
     * @param string $alert Session alert to show there
     * @return never
     */
    protected function redirect(string $path, string $alert = "") : never
    {
        if ($alert !== "") Session::SetAlert($alert);
        header("Location: " . Module::PathURL($path));
        exit;
    }

    /**
     * Text paragraph for a panel
     * @param string $text Text (escaped)
     * @return Component
     */
    protected static function Text(string $text) : Component
    {
        $cmp = new Component(false);
        $cmp->setTagName("p");
        $cmp->setComponentClass("text");
        $cmp->setText($text);
        return $cmp;
    }
}

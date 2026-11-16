<?php
include_once("templates/TemplateContent.php");

/**
 * Content of the account module. component() is a container of the page columns (AccountContent::Column()) -
 * AccountTemplatePage puts them next to the account menu.
 */
abstract class AccountContent extends TemplateContent
{
    public function __construct()
    {
        parent::__construct();
        $this->cmp = new Container(false);
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
        $this->cmp->items()->append($column);
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

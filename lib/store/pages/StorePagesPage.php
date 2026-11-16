<?php
include_once("store/pages/StoreTemplatePage.php");

/**
 * Page of the core pages module on the store sites (StoreTemplatePage: the storefront look).
 * Body class ContentPage (ContentPage.css, store_base.css '.ContentPage.pages'); the content columns (page menu,
 * page data) go into the main section.
 */
class StorePagesPage extends StoreTemplatePage
{
    public function __construct()
    {
        parent::__construct();

        $this->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/ContentPage.css");
        $this->body()->addClassName("ContentPage");
    }

    public function update(TemplateContent $content) : void
    {
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
}

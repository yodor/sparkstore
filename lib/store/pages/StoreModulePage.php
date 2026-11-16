<?php
include_once("store/pages/StoreTemplatePage.php");

/**
 * Page of the core public modules on the store sites (pages, news, faq) - StoreTemplatePage: the storefront look.
 * The body class and the stylesheet of the module (LOOK, by module name) - a site can add its own module to it or
 * set its own page class. The content columns go into the main section.
 */
class StoreModulePage extends StoreTemplatePage
{
    /**
     * Module name => array(body class, store stylesheet or "")
     * @var array<string, array>
     */
    public static array $LOOK = array(
        "pages" => array("ContentPage", "ContentPage.css"),
        "news" => array("NewsPage", "news.css"),
        "faq" => array("FAQPage", "faq.css"),
    );

    public function __construct()
    {
        parent::__construct();

        $look = StoreModulePage::$LOOK[Module::Active()->getName()] ?? array("", "");
        if ($look[0] !== "") $this->body()->addClassName($look[0]);
        if ($look[1] !== "") $this->head()->addCSS(Spark::Get(StoreConfig::STORE_LOCAL) . "/css/" . $look[1]);
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

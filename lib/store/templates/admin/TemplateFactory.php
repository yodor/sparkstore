<?php
include_once("store/utils/TemplateFactoryEvent.php");

class TemplateFactory
{
    public static $LocalPages = "class/templates/admin";
    public static $StorePages = "store/templates/admin";

    public static $LocalMenus = "class/templates/admin/menus";
    public static $StoreMenus = "store/templates/admin/menus";

    /**
     * Clsoure is called before $template->render()
     * @param string $templateClass
     * @param Closure|null $callble
     * @return void
     * @throws Exception
     */
    public static function RenderPage(string $templateClass): void
    {

        $local_file = TemplateFactory::$LocalPages."/".$templateClass.".php";
        $store_file = TemplateFactory::$StorePages."/".$templateClass.".php";

        if (stream_resolve_include_path($local_file)) {
            include_once($local_file);
        } else if (stream_resolve_include_path($store_file)) {
            include_once($store_file);
        }
        if (isset($template) && $template instanceof PageTemplate) {
            SparkEventManager::emit(new TemplateFactoryEvent(TemplateFactoryEvent::TEMPLATE_CREATED, $template));
            $template->render();
            SparkEventManager::emit(new TemplateFactoryEvent(TemplateFactoryEvent::TEMPLATE_RENDERED, $template));
        }
        else {
            throw new Exception("Unable to load template or template variable is not defined correctly");
        }
    }

    public static function MenuForPage(string $menuDefineClass): array
    {
        $local_file = TemplateFactory::$LocalMenus."/".$menuDefineClass.".php";
        $store_file = TemplateFactory::$StoreMenus."/".$menuDefineClass.".php";

        if (stream_resolve_include_path($local_file)) {
            include_once($local_file);
        } else if (stream_resolve_include_path($store_file)) {
            include_once($store_file);
        }

        if (isset($menu) && is_array($menu)) {
            return $menu;
        }
        Debug::ErrorLog("Menu can not be loaded for this path returning empty menu");
        return array();
    }

}

?>

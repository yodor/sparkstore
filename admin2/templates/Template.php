<?php
include_once("templates/TemplateConfig.php");
include_once("templates/TemplateContent.php");

final class Template
{
    private function __construct() {

    }

    public static function List(string $beanClass) : TemplateConfig
    {
        $config = new TemplateConfig();
        $config->beanClass = $beanClass;
        $config->contentClass = BeanList::class;
        return $config;
    }

    public static function Tree(string $beanClass) : TemplateConfig
    {
        $config = new TemplateConfig();
        $config->contentClass = BeanTree::class;

        $config->beanClass = $beanClass;
        return $config;
    }

    public static function Gallery(string $beanClass) : TemplateConfig
    {
        $config = new TemplateConfig();
        $config->contentClass = BeanGallery::class;

        $config->beanClass = $beanClass;
        return $config;
    }

    public static function Editor(string $beanClass, string $formClass) : TemplateConfig
    {
        $config = new TemplateConfig();
        $config->contentClass = BeanEditor::class;

        $config->beanClass = $beanClass;
        $config->formClass = $formClass;
        return $config;
    }

    public static function Content(string $path, ?TemplateConfig $config=null) : ?TemplateContent
    {
        $cmp = null;

        if ($config instanceof TemplateConfig) {

            if ($config->contentClass) {
                include_once("templates/$config->contentClass.php");
            }

            if (!is_null($config->observer)) {
                SparkEventManager::register(TemplateEvent::class, new SparkObserver($config->observer));
            }

            $cmp = new $config->contentClass();

            if ($cmp instanceof TemplateContent) {
                $cmp->configure($config);
            }

        }
        return $cmp;
    }
}
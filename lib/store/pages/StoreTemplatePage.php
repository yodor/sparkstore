<?php
include_once("class/pages/StorePage.php");
include_once("templates/Template.php");
include_once("templates/TemplateConfig.php");

/**
 * Storefront page of a module (account, checkout): the site StorePage look with the content of the module path.
 *
 * The path handling is the one of SparkTemplatePage - a StorePage can not extend it. A common module page
 * abstraction is to be decided when all pages run on the module engine (step 8 / 6.x review).
 *
 * The body gets the path as a css class: 'orders/details' → 'orders_details'.
 * Subclasses put the content in the page in update().
 */
abstract class StoreTemplatePage extends StorePage implements IObserver
{
    protected string $path = "home";

    public function __construct()
    {
        parent::__construct();

        $this->loginURL = Module::PathURL("login")->toString();

        SparkEventManager::register(TemplateEvent::class, $this);
        SparkEventManager::register(TemplateConfigEvent::class, $this);

        $this->addParameterName("path");
        //the module folder itself (/account/) is the 'home' path
        if (isset($_GET["path"]) && trim((string)$_GET["path"], "/") !== "") {
            $this->path = Template::FormatPath($_GET["path"], "/", true);
        }
    }

    /**
     * The module path of this request ('/orders/details')
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
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
     * Put the content in the page - after the content processed its input
     * @param TemplateContent $content
     * @return void
     */
    abstract public function update(TemplateContent $content) : void;

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

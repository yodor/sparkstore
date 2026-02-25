<?php

abstract class TemplateContent extends SparkObject
{
    protected ?TemplateConfig $config = null;
    protected ?Component $cmp = null;
    protected ?RequestParameterCondition $request_condition = null;
    protected ?DBTableBean $bean = null;

    protected ?SQLQuery $query = null;
    public function __construct()
    {
        parent::__construct();
        Spark::EnableBeanLocation("class/forms/");
        Spark::EnableBeanLocation("store/forms/");
        Spark::EnableBeanLocation("forms/");
    }

    public function setBean(DBTableBean $bean): void
    {
        $this->bean = $bean;
    }

    public function getBean(): ?DBTableBean
    {
        return $this->bean;
    }

    public function component(): Component
    {
        return $this->cmp;
    }

    public static function CreateAction(string $action, ?string $contents="", string $appendPath="") : Action
    {
        $act = new Action();
        $act->setURL(SparkTemplateAdminPage::Instance()->currentURL());
        $act->setAction($action);
        $act->getURL()->add(new URLParameter("action", $action));
        if (!is_null($contents)) {
            $act->setContents($contents);
        }
        if ($appendPath) {
            $pathParam = $act->getURL()->get("path");
            if ($pathParam instanceof URLParameter) {
                $pathParam->setValue($pathParam->value()."/".$appendPath);
            }
        }
        return $act;
    }

    abstract public function initialize() : void;


    abstract public function processInput() : void;

    /**
     * Fill the required actions
     */
    public function initPageActions(ActionCollection $collection) : void
    {

    }

    public function initPageFilters(Container $filters) : void
    {

    }

    public function setRequestCondition(RequestParameterCondition $condition): void
    {
        $this->request_condition = $condition;
    }
    public function getRequestCondition() : RequestParameterCondition
    {
        return $this->request_condition;
    }

    public function configure(TemplateConfig $config)
    {
        $this->config = $config;

        if ($config->beanClass) {
            Spark::LoadBeanClass($config->beanClass);
            $this->setBean(new $config->beanClass());
        }
        if ($config->condition) {
            $this->setRequestCondition($config->condition);
        }

    }
    public function getConfig() : TemplateConfig
    {
        return $this->config;
    }

}
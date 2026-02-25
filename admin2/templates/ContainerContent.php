<?php
include_once("templates/TemplateContent.php");

class ContainerContent extends TemplateContent
{


    public function __construct()
    {
        parent::__construct();
        $this->cmp = new Container();
    }

    public function initialize(): void
    {
        // TODO: Implement initialize() method.
    }

    public function component(): Component
    {
        return $this->cmp;
    }

    public function processInput(): void
    {
        // TODO: Implement processInput() method.
    }

    public function initPageActions(ActionCollection $collection): void
    {
        // TODO: Implement initPageActions() method.
    }

    public function initPageFilters(Container $filters): void
    {
        // TODO: Implement initPageFilters() method.
    }

    public function configure(TemplateConfig $config)
    {
        parent::configure($config);
        $this->cmp->items()->append(new TextComponent($config->textContents));
    }
}
<?php
include_once("components/Container.php");

class TogglePanel extends Container {

    protected Container $viewport;

    protected Component $toggleTitle;

    public function __construct()
    {
        parent::__construct(false);
        $this->setComponentClass("panel");

        $caption = new Container(false);
        $caption->setComponentClass("Caption");
        $this->items()->append($caption);

        $this->toggleTitle = new Component(false);
        $this->toggleTitle->setTagName("span");
        $this->toggleTitle->setComponentClass("label");
        $this->toggleTitle->setContents("");

        $icon = new Component(false);
        $icon->setTagName("span");
        $icon->setComponentClass("icon");

        $toggle = new Container(false);
        $toggle->setComponentClass("toggle");
        $toggle->setAttribute("onClick", "togglePanel(this)");
        $toggle->items()->append($icon);
        $toggle->items()->append($this->toggleTitle);
        $caption->items()->append($toggle);

        $caption->items()->append($this->toggleTitle);

        $this->viewport = new Container(false);
        $this->viewport->setComponentClass("viewport");
        $this->items()->append($this->viewport);
    }

    public function setTitle(string $text): void
    {
        $this->toggleTitle->setContents($text);
    }
    public function getTitle(): string
    {
        return $this->toggleTitle->getContents();
    }
    public function getViewport(): Container
    {
        return $this->viewport;
    }
}
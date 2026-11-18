<?php
include_once("store/components/frame/StoreSection.php");
include_once("components/Button.php");
include_once("components/TextComponent.php");

/**
 * The cookies consent panel of the store frame (accepted by document.sparkCookies - store js/cookies.js)
 */
class StoreCookies extends StoreSection
{
    public function __construct()
    {
        parent::__construct();
        $this->addClassName("cookies");
        $this->setTagName("aside");
        $this->setAttribute("aria-label", "Cookie Consent Notification");
        $this->setAttribute("role", "dialog");
        $this->setAttribute("tabindex", "0");
    }

    public function fill(Container $content, StorePageBase $page) : void
    {
        $content->items()->append(StoreCookies::InfoPanel());
    }

    /**
     * The panel: the 'Добре' button and the text
     * @return Container
     */
    public static function InfoPanel() : Container
    {
        $container = new Container(false);
        $container->setComponentClass("info");

        $button = Button::Action(tr("Добре"), "javascript:document.sparkCookies.accept()");
        $button->setComponentClass("ColorButton");
        $container->items()->append($button);

        $container->items()->append(new TextComponent(tr("Сайтът използва '<i>бисквитки</i>', за да подобри услугите. Продължавайки разглеждането му автоматично се съгласявате с тяхното използване.")));

        return $container;
    }
}

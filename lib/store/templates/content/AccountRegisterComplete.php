<?php
include_once("store/templates/content/AccountContent.php");

/**
 * After the registration - the activation mail was sent
 */
class AccountRegisterComplete extends AccountContent
{
    public function initialize() : void
    {
        $message = new Container(false);
        $message->setComponentClass("success_message");

        $tick = new Component(false);
        $tick->setComponentClass("tick_mark");
        $message->items()->append($tick);

        $title = new Component(false);
        $title->setTagName("h1");
        $title->setComponentClass("Caption");
        $title->setText(tr("Благодарим Ви че се регистрирахте при нас"));
        $message->items()->append($title);

        foreach (array(tr("Ще получите e-mail за активация на профила си."),
                     tr("След активация ще можете да променяте своята лична информация и адрес за доставка.")) as $text) {
            $line = new Component(false);
            $line->setTagName("span");
            $line->setText($text);
            $message->items()->append($line);
        }

        $this->addColumn(AccountContent::Column(tr("Успешна регистрация"), $message));
    }

    protected function getContentTitle() : string
    {
        return tr("Успешна регистрация");
    }
}

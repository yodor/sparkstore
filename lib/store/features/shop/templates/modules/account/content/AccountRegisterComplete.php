<?php
include_once("store/features/shop/templates/modules/account/content/AccountContent.php");

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
        $title->setText(tr("Thank you for registering with us"));
        $message->items()->append($title);

        foreach (array(tr("You will receive an email to activate your profile."),
                     tr("After the activation you can change your personal details and delivery address.")) as $text) {
            $line = new Component(false);
            $line->setTagName("span");
            $line->setText($text);
            $message->items()->append($line);
        }

        $this->addColumn(AccountContent::Column(tr("Registration complete"), $message));
    }

    protected function getContentTitle() : string
    {
        return tr("Registration complete");
    }
}

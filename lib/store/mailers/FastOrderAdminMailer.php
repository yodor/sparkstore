<?php
include_once("mailers/Mailer.php");
include_once("store/forms/ClientAddressInputForm.php");
include_once("store/utils/Cart.php");
include_once("store/components/CartComponent.php");

class FastOrderAdminMailer extends Mailer
{

    public function __construct(ClientAddressInputForm $form)
    {

        parent::__construct();


        debug ("Preparing message ...");

        $this->to = ORDER_ADMIN_EMAIL;
        $this->subject = "Бърза поръчка на ".SITE_DOMAIN;

        $message = "Здравейте, \r\n\r\n";
        $message .= "Беше направена бърза поръчка на ". SITE_DOMAIN;
        $message .= "\r\n\r\n";

        $message .= "Поръчани продукти:\r\n\r\n";

        $cart_render = new CartComponent();
        $cart_render->setModifyEnabled(false);
        $cart_render->getTable()->setAttribute("border", "1");


        ob_start();
        $cart_render->render();
        $cart_contents = ob_get_contents();
        ob_end_clean();

        $message .= $cart_contents;

        $message .= "\r\n\r\n";

        $message .= "Клиент Име: ".$form->getInput("fullname")->getValue();
        $message .= "\r\n";
        $message .= "Клиент Телефон: ".$form->getInput("phone")->getValue();
        $message .= "\r\n";

        $this->body = $this->templateMessage($message);

        debug ("Message contents prepared ...");


    }

}

?>

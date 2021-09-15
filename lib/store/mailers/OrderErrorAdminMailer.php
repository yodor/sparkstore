<?php
include_once("mailers/Mailer.php");

class OrderErrorAdminMailer extends Mailer
{

    public function __construct(string $error_text)
    {
        parent::__construct();

        $this->to = ORDER_ERROR_EMAIL;
        $this->subject = "Order Error";

        // 	ob_start();
        // 	var_dump($row);
        // 	$message = ob_get_contents();
        // 	ob_end_clean();

        $message = "Hello, \r\n";
        $message.= "There was an error during order finalization at ".SITE_URL;
        $message.= "\r\n";
        $message.= $error_text;

        $this->body = $this->templateMessage($message);

    }

}

?>

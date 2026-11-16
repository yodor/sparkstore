<?php
include_once("mailers/Mailer.php");

class OrderErrorAdminMailer extends Mailer
{

    public function __construct(string $error_text)
    {
        parent::__construct();

        //not set in config/store.php - the shop's order address
        $this->to = Spark::Get(StoreConfig::ORDER_ERROR_EMAIL) ?: Spark::Get(StoreConfig::ORDER_EMAIL);
        $this->subject = "Order Error at " . Spark::Get(Config::SITE_DOMAIN);

        // 	ob_start();
        // 	var_dump($row);
        // 	$message = ob_get_contents();
        // 	ob_end_clean();

        $message = "Hello, \r\n";
        $message.= "There was an error during order finalization at ".Spark::Get(Config::SITE_URL);
        $message.= "\r\n";
        $message.= htmlspecialchars($error_text, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");

        $this->body = $message; //send() wraps it in the HTML document

    }

}
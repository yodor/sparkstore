<?php
include_once("responders/json/JSONResponder.php");

class AdminHelpResponder extends JSONResponder
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function parseParams(): void
    {
        parent::parseParams();
        if (!isset($_GET["path"])) throw new Exception("No path specified");
    }

    public function _fetch(JSONResponse $response) : void
    {
        $file = Spark::Split($_GET["path"],"/");
        $file = implode(".", $file);

        $file = "help/".$file.".html";

        if (file_exists($file)) {
            $response->message = file_get_contents($file);
        }
        else {
            $response->message = "Help file not found: ".$file;
        }


    }

}
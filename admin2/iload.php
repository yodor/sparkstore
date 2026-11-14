<?php
include_once("utils/SessionData.php");
include_once("objects/SparkSealed.php");
include_once("responders/RequestController.php");

$data = new SessionData("load-store-test");
$object = $data->get("responder");
Session::Close();

echo get_class($object);


//response
//Module::Response();
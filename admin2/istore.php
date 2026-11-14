<?php
include_once("utils/SessionData.php");
include_once("objects/SparkSealed.php");
include_once("responders/DeleteItemResponder.php");
include_once("store/beans/ProductsBean.php");

include_once("responders/RequestController.php");
$object = new DeleteItemResponder(new ProductsBean());

$data = new SessionData("load-store-test");
$data->set("responder", $object);

$result = array();
SparkLoader::ClassMap($object, $result);
var_dump(array_keys($result));
//response
//Module::Response();
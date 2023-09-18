<?php
define("SKIP_SESSION",1);
define("SKIP_DB",1);
define("SKIP_TRANSLATOR",1);

include_once("utils/SparkFile.php");
include_once("storage/SparkFileHTTPResponse.php");

$user_logo = new SparkFile("logo");
$user_logo->setPath(CACHE_PATH);

$origin_logo = new SparkFile(LOGO_NAME);
$origin_logo->setPath(LOGO_PATH);

$response = new SparkFileHTTPResponse();
if ($user_logo->exists()) {
    $response->setFile($user_logo);
    $response->send();
}
else {
    $response->setFile($origin_logo);
    $response->send();

}
exit;

?>
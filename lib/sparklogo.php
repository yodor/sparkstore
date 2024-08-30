<?php
define("SKIP_SESSION",1);
define("SKIP_DB",1);
define("SKIP_TRANSLATOR",1);

include_once("utils/SparkFile.php");
include_once("storage/SparkHTTPResponse.php");

$user_logo = new SparkFile("logo");
$user_logo->setPath(CACHE_PATH);

$origin_logo = new SparkFile(LOGO_NAME);
$origin_logo->setPath(LOGO_PATH);

$response = new SparkHTTPResponse();
if ($user_logo->exists()) {
    $response->sendFile($user_logo);

}
else {
    $response->sendFile($origin_logo);
}
exit;

?>

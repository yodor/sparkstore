<?php
include_once("utils/SparkFile.php");
include_once("storage/SparkHTTPResponse.php");

$user_logo = new SparkFile();
$user_logo->setFilename("logo");
$user_logo->setPath(CACHE_PATH);

$origin_logo = new SparkFile();
$origin_logo->setFilename(LOGO_NAME);
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

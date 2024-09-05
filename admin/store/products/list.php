<?php
include_once("session.php");
//called before render
$callback = function(PageTemplate $template) {
    $page = $template->getPage();
    if($page instanceof SparkAdminPage) {
        $page->navigation()->clear();
    }

};
TemplateFactory::RenderPage("ProductsList", $callback);

?>

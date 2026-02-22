<?php
include_once("class/pages/NewsPage.php");

$page = new NewsPage();

$page->getPublications()->processInput();

$page->startRender();
$page->finishRender();
<?php
include_once("class/pages/NewsPage.php");

$page = new NewsPage();

$page->initialize();

$page->render();
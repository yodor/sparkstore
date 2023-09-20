<?php
include_once("session.php");
include_once ("templates/admin/PhraseTranslatorPage.php");

$cmp = new PhraseTranslatorPage();
$cmp->render();


?>
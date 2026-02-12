<?php
include_once("session.php");
include_once("components/templates/admin/PhraseTranslatorPage.php");

$cmp = new PhraseTranslatorPage();
$cmp->render();
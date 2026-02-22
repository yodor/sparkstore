<?php
include_once("class/pages/AccountPage.php");
include_once("beans/UsersBean.php");
include_once("store/forms/ActivateProfileInputForm.php");
include_once("store/forms/processors/ActivateProfileFormProcessor.php");

$page = new AccountPage(FALSE);
$page->setTitle(tr("Активация на профил"));

$form = new ActivateProfileInputForm();

$frend = new FormRenderer($form);
$frend->setMethod(FormRenderer::METHOD_GET);

$proc = new ActivateProfileFormProcessor();

$proc->process($form);


if ($proc->getStatus() === IFormProcessor::STATUS_OK) {
    Session::SetAlert(tr("Успешна активация на профил"));
    header("Location: login.php");
    exit;
}
else if ($proc->getStatus() === IFormProcessor::STATUS_ERROR) {
    Session::SetAlert(tr("Възникна грешка при активация на профила")."<div class='error'>".$proc->getMessage()."</div>");
}


$page->startRender();


echo "<div class='column'>";

echo "<h1 class='Caption'>".tr("Активация на профил")."</h1>";
$frend->render();

echo "</div>"; //column


$page->finishRender();
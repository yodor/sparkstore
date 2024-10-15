<?php
include_once("session.php");
include_once("templates/admin/ConfigEditorPage.php");
include_once("storage/SparkFile.php");

$page = new AdminPage();
$page->setName("Logo Management");
$page->navigation()->clear();



$current = new SparkFile("logo");
$current->setPath(CACHE_PATH);

$origin_file = new SparkFile(LOGO_NAME);
$origin_file->setPath(LOGO_PATH);

$image_input = DataInputFactory::Create(DataInputFactory::SESSION_IMAGE, "logo", "Upload logo image (450px x 90px)", 1);

if (isset($_GET["restore"])) {
    $current->remove();
    $image_input->clear();
    header("Location: list.php");
    exit;
}

$form = new InputForm();
$form->addInput($image_input);
$frend = new FormRenderer($form);
$proc = new FormProcessor();
$proc->process($form);

if ($proc->getStatus() == IFormProcessor::STATUS_OK) {
    $is = $form->getInput("logo")->getValue()[0];
    if ($is instanceof ImageStorageObject) {
        $current->open('w');
        $current->write($is->data());
        $current->close();
        $image_input->clear();
    }
}

$page->startRender();

if ($current->exists()) {
    echo "<div class='logo_header current'>";
    echo "<label>".tr("Current logo")."</label>";
    echo "<img src='data:{$current->getMIME()};base64, {$current->getBase64()}'>";
    echo "</div>";
}

if ($origin_file->exists()) {
    echo "<div class='logo_header origin'>";
    echo "<label>" . tr("Origin logo") . "</label>";
    echo "<img src='data:{$origin_file->getMIME()};base64, {$origin_file->getBase64()}'>";
    if ($current->exists()) {
        echo "<a class='ColorButton' action='submit' href='?restore'>" . tr("Restore") . "</a>";
    }
    echo "</div>";
}
echo "<hr>";
$frend->render();

$page->finishRender();

?>

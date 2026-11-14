<?php
include_once("templates/TemplateContent.php");
include_once("storage/SparkFile.php");
include_once("components/ClosureComponent.php");
include_once("forms/renderers/FormRenderer.php");
include_once("forms/processors/FormProcessor.php");
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

/**
 * Site logo: upload replaces CACHE_PATH/logo, 'restore' removes it so the original logo
 * (StoreConfig::LOGO_PATH / LOGO_NAME) is used again
 */
class LogoEditor extends TemplateContent
{
    const string KEY_RESTORE = "restore";

    protected SparkFile $current;
    protected SparkFile $origin;

    protected InputForm $form;
    protected DataInput $input;
    protected FormProcessor $processor;

    public function __construct()
    {
        parent::__construct();

        $this->current = new SparkFile();
        $this->current->setFilename("logo");
        $this->current->setPath(Spark::Get(Config::CACHE_PATH));

        $this->origin = new SparkFile();
        $this->origin->setFilename(Spark::Get(StoreConfig::LOGO_NAME));
        $this->origin->setPath(Spark::Get(StoreConfig::LOGO_PATH));

        $this->input = DataInputFactory::Create(InputType::SESSION_IMAGE, "logo", "Upload logo image (450px x 90px)", 1);
        $this->form = new InputForm();
        $this->form->addInput($this->input);
        $this->processor = new FormProcessor();
    }

    /**
     * This module path without the restore parameter
     * @return URL
     */
    protected function pathURL() : URL
    {
        $url = Module::PathURL("", URL::Current());
        $url->remove(LogoEditor::KEY_RESTORE);
        return $url;
    }

    public function processInput(): void
    {
        if (isset($_GET[LogoEditor::KEY_RESTORE])) {
            $this->current->remove();
            $this->input->clear();
            header("Location: " . $this->pathURL());
            exit;
        }

        $this->processor->process($this->form);
        if ($this->processor->getStatus() == IFormProcessor::STATUS_OK) {
            $object = $this->form->getInput("logo")->getValue()[0] ?? null;
            if ($object instanceof ImageStorageObject) {
                $this->current->replace($object->data());
                $this->input->clear();
                Session::SetAlert(tr("Logo updated"));
                header("Location: " . $this->pathURL());
                exit;
            }
        }
    }

    public function initialize(): void
    {
        $current = $this->current;
        $origin = $this->origin;
        $restoreURL = $this->pathURL();
        $restoreURL->add(new URLParameter(LogoEditor::KEY_RESTORE, "1"));

        $previews = new ClosureComponent(function () use ($current, $origin, $restoreURL) {
            if ($current->exists()) {
                echo "<div class='logo_header current'>";
                echo "<label>" . tr("Current logo") . "</label>";
                echo "<img src='data:{$current->getMIME()};base64,{$current->getBase64()}'>";
                echo "</div>";
            }
            if ($origin->exists()) {
                echo "<div class='logo_header origin'>";
                echo "<label>" . tr("Origin logo") . "</label>";
                echo "<img src='data:{$origin->getMIME()};base64,{$origin->getBase64()}'>";
                if ($current->exists()) {
                    echo "<a class='ColorButton' action='submit' href='$restoreURL'>" . tr("Restore") . "</a>";
                }
                echo "</div>";
            }
            echo "<hr>";
        });

        $this->cmp = new Container(false);
        $this->cmp->items()->append($previews);
        $this->cmp->items()->append(new FormRenderer($this->form));
    }

    protected function getContentTitle(): string
    {
        return tr("Logo Management");
    }
}

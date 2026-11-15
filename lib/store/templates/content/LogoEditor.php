<?php
include_once("templates/TemplateContent.php");
include_once("storage/SparkFile.php");
include_once("components/Image.php");
include_once("components/Action.php");
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

        $this->cmp = new Container(false);
        if ($current->exists()) {
            $this->cmp->items()->append($this->preview("current", tr("Current logo"), $current));
        }
        if ($origin->exists()) {
            $originPreview = $this->preview("origin", tr("Origin logo"), $origin);
            if ($current->exists()) {
                $restore = new Action("submit", $restoreURL->toString());
                $restore->setClassName("ColorButton");
                $restore->setContents(tr("Restore"));
                $originPreview->items()->append($restore);
            }
            $this->cmp->items()->append($originPreview);
        }
        $line = new Component(false);
        $line->setTagName("hr");
        $line->setComponentClass("");
        $line->setClosingTagRequired(false);
        $this->cmp->items()->append($line);
        $this->cmp->items()->append(new FormRenderer($this->form));
    }

    /**
     * <div class='logo_header <name>'><label>..</label><img src='data:..'></div>
     * @param string $name
     * @param string $label
     * @param SparkFile $file
     * @return Container
     */
    protected function preview(string $name, string $label, SparkFile $file) : Container
    {
        $preview = new Container(false);
        $preview->setComponentClass("logo_header " . $name);

        $caption = new Component(false);
        $caption->setTagName("label");
        $caption->setComponentClass("");
        $caption->setText($label);
        $preview->items()->append($caption);

        $image = new Image();
        $image->setAttribute("src", "data:" . $file->getMIME() . ";base64," . $file->getBase64());
        $preview->items()->append($image);

        return $preview;
    }

    protected function getContentTitle(): string
    {
        return tr("Logo Management");
    }
}

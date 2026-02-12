<?php
include_once("forms/InputForm.php");
include_once("store/beans/SectionsBean.php");

class ImportUpdateChooserForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();


        $field = DataInputFactory::Create(InputType::SESSION_FILE, "update_file", "Select CSV update file", 1);
        $field->getProcessor()->setTransactBeanItemLimit(1);
        $arrValidator = $field->getValidator();
        if ($arrValidator instanceof ArrayInputValidator) {
            $validator = $arrValidator->getItemValidator();
            if ($validator  instanceof UploadDataValidator) {
                $validator->setAcceptMimes(array("text/csv", "text/html"));
                Debug::ErrorLog("Setting accept mimes to text/csv");
            }
        }

        $this->addInput($field);


    }


}
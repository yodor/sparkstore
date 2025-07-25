<?php
include_once("forms/InputForm.php");
include_once("store/beans/SectionsBean.php");

class ImportUpdateChooserForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();


        $field = DataInputFactory::Create(DataInputFactory::SESSION_FILE, "update_file", "Select CSV update file", 1);
        $field->getProcessor()->setTransactBeanItemLimit(1);
        $validator = $field->getValidator();
        if ($validator instanceof UploadDataValidator) {
            $validator->setAcceptMimes(array("text/csv"));
        }
        $this->addInput($field);


    }


}
?>
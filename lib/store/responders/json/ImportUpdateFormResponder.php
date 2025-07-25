<?php
include_once("responders/json/JSONFormResponder.php");
include_once("store/forms/ImportUpdateChooserForm.php");


class ImportUpdateFormResponder extends JSONFormResponder
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function createForm(): InputForm
    {
        return new ImportUpdateChooserForm();
    }

    protected function onProcessSuccess(JSONResponse $resp)
    {
        parent::onProcessSuccess($resp);
        $uploadFiles = $this->form->getInput("update_file")->getValue();

        $file = $uploadFiles[0];
        if (!($file instanceof FileStorageObject)) {
            throw new Exception("Incorrect upload file");
        }


        $separator = ",";
        $enclosure = '"';
        $escape = "\\";
        $eol = PHP_EOL;


        $stream = fopen('data://text/plain,' . $file->data(),'r');



        $bean = new ProductsBean();

        $db = DBConnections::Open();

        try {

            $db->transaction();

            $linePosition = 0;

            while (($line = fgetcsv($stream, 0, $separator, $enclosure, $escape)) !== FALSE) {
                $linePosition++;
                if (count($line) != 3) throw new Exception("Incorrect number of columns. Expected 3 columns");

                if ($linePosition == 1) {
                    if (strcmp($line[0], "prodID") != 0 || strcmp($line[1], "product_name") != 0 || strcmp($line[2], "product_description") != 0) {
                        throw new Exception("Incorrect column keys names. Required keys: prodID, product_name, product_description");
                    }
                    continue;
                }

                $prodID = (int)$line[0];
                $updateData = array("product_name"=>$line[1], "product_description"=>$line[2]);

                $bean->update($prodID, $updateData, $db);

            }

            $db->commit();
            $resp->message = "Updated ".$linePosition." products.";
        }
        catch (Exception $e) {
            $db->rollback();
            throw $e;
        }

    }

}
?>
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

    protected function onProcessSuccess(JSONResponse $resp): void
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

        $stream = fopen("data:text/plain," . $file->data(),"r");
        if (!$stream) throw new Exception("Unable to open uploaded file as stream");

        try {

            //skip BOM if any
            if (fread($stream, 3) !== "\xEF\xBB\xBF") {
                // if there is no BOM rewind
                rewind($stream);
            }
            else {
                Debug::ErrorLog("BOM UTF-8 skipped");
            }

            //read key column names
            $line = fgetcsv($stream, 0, $separator, $enclosure, $escape);

            if (!is_array($line)) {
                throw new Exception("Incorrect CSV format of file");
            }

            $keyNames = array(0 => "prodID", 1 => "product_name", 2 => "product_description", 3 => "seo_description");

            $keyCount = count($keyNames);
            if (count($line) != $keyCount) {
                throw new Exception("Incorrect number of key columns");
            }

            for ($i = 0; $i < $keyCount; $i++) {
                if (strcmp($line[$i], $keyNames[$i]) !== 0) {
                    throw new Exception("Incorrect column keys name: " . $line[$i]);
                }
            }


            $bean = new ProductsBean();

            $db = DBConnections::Open();

            try {

                $db->transaction();

                $productsUpdated = 0;

                while (($line = fgetcsv($stream, 0, $separator, $enclosure, $escape)) !== FALSE) {

                    if (count($line) != $keyCount) throw new Exception("Incorrect number of columns. Expected $keyCount columns");

                    $prodID = (int)$line[0];
                    $productName = Spark::SanitizeInput($line[1]);
                    $productDescription = Spark::SanitizeInput($line[2]);
                    $seoDescription = Spark::SanitizeInput($line[3]);
                    $updateData = array("product_name" => $productName,
                        "product_description" => $productDescription,
                        "seo_description" => $seoDescription,
                    );

                    Debug::ErrorLog("Going to update prodID: $prodID ...");

                    $bean->update($prodID, $updateData, $db);
                    $productsUpdated++;
                }

                $db->commit();
                $resp->message = "Updated " . $productsUpdated . " products.";

                fclose($stream);

            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
        }
        catch (Exception $e) {
            fclose($stream);
            throw $e;
        }

    }

}
?>
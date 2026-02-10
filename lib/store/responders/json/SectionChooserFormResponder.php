<?php
include_once("responders/json/JSONFormResponder.php");
include_once("store/forms/SectionChooserForm.php");
include_once("store/beans/SectionsBean.php");
include_once("store/beans/ProductSectionsBean.php");

class SectionChooserFormResponder extends JSONFormResponder
{
    protected int $prodID = -1;

    public function __construct()
    {
        parent::__construct();
    }

    protected function createForm(): InputForm
    {
        return new SectionChooserForm();
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function parseParams() : void
    {
        parent::parseParams();

        if (!isset($_REQUEST["prodID"])) throw new Exception("Parameter 'prodID' not specified");

        $this->prodID = (int)$_REQUEST["prodID"];

        //load selected sections into the form value
        $select = new SQLSelect();
        $select->fields()->set("ps.prodID", "ps.secID");
        $select->from = " product_sections ps  ";
        $select->where()->add("ps.prodID", $this->prodID);

        $query = new SQLQuery($select, "psID");
        $num = $query->exec();
        $values = array();
        while ($result = $query->nextResult()) {
            $values[] = $result->get("secID");
        }
        $this->form->getInput("secID")->setValue($values);


    }

    protected function onProcessSuccess(JSONResponse $resp): void
    {
        parent::onProcessSuccess($resp);
        $sections = $this->form->getInput("secID")->getValue();

        //update selected sections into the bean
        $field = $this->form->getInput("secID");
        if (! ($field instanceof ArrayDataInput)) throw new Exception("Incorrect data type");

        $db = DBConnections::Open();
        try {
            $db->transaction();

            //delete all sections of this product and insert posted sections only
            $delete = new SQLDelete();
            $delete->from = " product_sections ";
            $delete->where()->add("prodID", $this->prodID);

            $db->query($delete->getSQL());

            $insert = new SQLInsert();
            $insert->from = " product_sections ";

            $section_ids = $field->getValues();

            if (count($section_ids) > 0) {

                //initialize columns
                $insert->fields()->setColumn(new SQLColumn("prodID"));
                $insert->fields()->setColumn(new SQLColumn("secID"));

                foreach ($section_ids as $idx=>$secID) {
                    $insert->fields()->getColumn("prodID")->addValue($this->prodID);
                    $insert->fields()->getColumn("secID")->addValue($secID);
                }

                $db->query($insert->getSQL());
            }

            $db->commit();

            $resp->message = tr("Information was updated");
        }
        catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

}
?>
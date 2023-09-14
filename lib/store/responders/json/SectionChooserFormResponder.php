<?php
include_once("responders/json/JSONFormResponder.php");
include_once("store/forms/SectionChooserForm.php");
include_once("store/beans/SectionsBean.php");
include_once("store/beans/ProductSectionsBean.php");

class SectionChooserFormResponder extends JSONFormResponder
{
    protected $prodID = -1;

    public function __construct()
    {
        parent::__construct("SectionChooserFormResponder");
    }

    protected function createForm(): InputForm
    {
        return new SectionChooserForm();
    }

    protected function parseParams()
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

    protected function onProcessSuccess(JSONResponse $resp)
    {
        parent::onProcessSuccess($resp);
        $sections = $this->form->getInput("secID")->getValue();

        //update selected sections into the bean

        $db = DBConnections::Get();
        try {
            $db->transaction();

            $delete = new SQLDelete();
            $delete->from = " product_sections ";
            $delete->where()->add("prodID", $this->prodID);

            if (!$db->query($delete->getSQL())) throw new Exception("Unable to delete old values: ".$db->getError());

            debug("Posted: ", $_POST);

            $sql = "INSERT INTO product_sections (prodID, secID) VALUES ";
            $field = $this->form->getInput("secID");
            if ($field instanceof ArrayDataInput) {
                $section_ids = $field->getValues();
                debug("Values Count: ".$field->getValuesCount());
//                var_dump($_POST);
            }
            else {
                throw new Exception("Incorrect data type");
            }

            $values = array();

            foreach ($section_ids as $idx=>$secID) {
                $values[] = "({$this->prodID}, $secID)";
            }
            $sql.= implode(",", $values);

            if (!$db->query($sql)) throw new Exception("Unable to insert new values: ".$db->getError());
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
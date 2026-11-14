<?php
include_once("forms/processors/FormProcessor.php");
include_once("sql/SQLDelete.php");
include_once("sql/SQLInsert.php");
include_once("iterators/SelectQuery.php");

/**
 * Loads and stores the posted variant options of one product (table product_variants)
 */
class ProductVariantsProcessor extends FormProcessor
{
    protected int $prodID = -1;


    public function __construct(int $prodID)
    {
        parent::__construct();
        $this->prodID = $prodID;
    }

    protected function processImpl(InputForm $form) : void
    {
        parent::processImpl($form);
        $this->storeFormData($form);
    }


    protected function storeFormData(InputForm $form) : void
    {

        $db = DBManager::Driver();

        try {

            $db->transaction();

            $posted_voIDs = array();

            foreach ($form->inputValues() as $idx=> $values) {

                if (!is_array($values)) continue;
                foreach ($values as $voID=>$value) {
                    if ($value) {
                        $posted_voIDs[] = $voID;
                    }
                }

            }

            if (is_array($posted_voIDs) && count($posted_voIDs)>0) {
                //echo "<pre>Posted IDS: ".print_r($posted_voIDs)."</pre>";

                $delete = SQLDelete::Table("product_variants");
                $delete->where()->match("prodID", $this->prodID);

                $idlist = $delete->where()->bindList($posted_voIDs);
                $delete->where()->expression("voID NOT IN ($idlist)");

                $db->query($delete)->free();

                $insert = SQLInsert::Table("product_variants");
                $col_prodID = $insert->columnArray("prodID");
                $col_voID = $insert->columnArray("voID");

                foreach ($posted_voIDs as $idx=>$voID) {
                    $col_prodID->addValue($this->prodID);
                    $col_voID->addValue($voID);
                }

                $db->query($insert)->free();

            }
            else {

                //clear all variants
                $delete = SQLDelete::Table("product_variants");
                $delete->where()->match("prodID", $this->prodID);

                $db->query($delete)->free();
            }

            $db->commit();
        }
        catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public function loadFormData(InputForm $form)
    {

        $select = new SQLSelect();
        $select->from("product_variants pv")->join("variant_options vo")->on("vo.voID = pv.voID ");
        $select->columns("vo.voID", "vo.option_name", "vo.option_value", "vo.parentID");
        $select->where()->match("pv.prodID", $this->prodID);

        $query = new SelectQuery($select, "pvID");
        $query->exec();

        foreach ($form->inputs() as $idx=> $input) {
            $input->setValue(array());
        }

        while ($result = $query->nextResult()) {
            $input_name = "voID_".$result->get("parentID");
            $value = $result->get("option_value");
            $voID = $result->get("voID");

            $values = $form->getInput($input_name)->getValue();
            $values[$voID] = $value;
            $form->getInput($input_name)->setValue($values);
        }

        $query->free();
    }
}

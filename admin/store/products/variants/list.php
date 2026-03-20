<?php
include_once("components/templates/admin/BeanListPage.php");

include_once("store/beans/VariantOptionsBean.php");
include_once("store/beans/ProductClassesBean.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ProductVariantsBean.php");

include_once("store/responders/json/VariantPriceFormResponder.php");
include_once("input/renderers/CheckField.php");
include_once("input/validators/EmptyValueValidator.php");
include_once("input/processors/InputProcessor.php");


//TODO: Triggers on update/delete for colors and sizes

class ProductVariantsInputForm extends InputForm
{
    protected ?VariantOptionsBean $voptions = null;

    public function __construct(int $prodID)
    {
        parent::__construct();

        $this->voptions = new VariantOptionsBean();

        $group_normal = new InputGroup("normal_options", "Основни опции");
        $this->addGroup($group_normal);

        $group_class = new InputGroup("class_options", "Опции от продуктовия клас");
        $this->addGroup($group_class);

        $group_product = new InputGroup("product_options", "Други опции на продукта");
        $this->addGroup($group_product);

        $products = new ProductsBean();
        $product = $products->getByID($prodID, "pclsID");

        ///base options
        $query = $this->voptions->queryFull();
        $query->stmt->where()->addExpression("pclsID IS NULL");
        $query->stmt->where()->addExpression("parentID IS NULL");
        $query->stmt->where()->addExpression("prodID IS NULL");
        $query->stmt->where()->addExpression("option_value IS NULL");

        $query->exec();
        while ($result = $query->nextResult()) {
            $voID = $result->get("voID");
            $option_name = $result->get("option_name");

            $input = new DataInput("voID_$voID", $option_name, 0);
            $this->createInputIterator($input, $voID);
            $this->addInput($input, $group_normal);
        }
        $query->free();

        ///options from product class
        $pclsID = (int)$product["pclsID"];
        if ($pclsID>0) {
            $query = $this->voptions->queryFull();
            $query->stmt->where()->add("pclsID" , $pclsID);
            $query->stmt->where()->addExpression("parentID IS NULL");
            $query->stmt->where()->addExpression("option_value IS NULL");
            $query->stmt->where()->addExpression("prodID IS NULL");

            $query->exec();
            while ($result = $query->nextResult()) {
                $voID = $result->get("voID");
                $option_name = $result->get("option_name");

                $input = new DataInput("voID_$voID", $option_name, 0);
                $this->createInputIterator($input, $voID);
                $this->addInput($input, $group_class);
            }
            $query->free();
        }

        ///options for this product
        $query = $this->voptions->queryFull();
        $query->stmt->where()->add("prodID" , $prodID);
        $query->stmt->where()->addExpression("parentID IS NULL");
        $query->stmt->where()->addExpression("option_value IS NULL");
        $query->stmt->where()->addExpression("pclsID IS NULL");

        $query->exec();
        while ($result = $query->nextResult()) {
            $voID = $result->get("voID");
            $option_name = $result->get("option_name");

            $input = new DataInput("voID_$voID", $option_name, 0);
            $this->createInputIterator($input, $voID);
            $this->addInput($input, $group_product);
        }
        $query->free();

    }

    protected function createInputIterator(DataInput $input, int $parentID)
    {
        $validator = new EmptyValueValidator();
        $input->setValidator($validator);

        $query_parameters = $this->voptions->queryFull();
        $query_parameters->stmt->where()->add("parentID" , $parentID);

        $cf3 = new CheckField($input);
        $cf3->setArrayKeyFieldName("voID");
        $cf3->setIterator($query_parameters);
        $cf3->getItemRenderer()->setValueKey("option_value");
        $cf3->getItemRenderer()->setLabelKey("option_value");
        new InputProcessor($input);
    }

}

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

        $db = DBConnections::CreateDriver();

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
                $delete->where()->add("prodID", $this->prodID);

                $idlist = $delete->bindList($posted_voIDs);
                $delete->where()->addExpression("voID NOT IN ($idlist)");

                $db->query($delete)->free();

                //insert non-existing IDs - multi-insert initialized value to []
                $col_prodID = new SQLColumn("prodID", []);
                $col_voID = new SQLColumn("voID", []);

                foreach ($posted_voIDs as $idx=>$voID) {
                    $col_prodID->addValue($this->prodID);
                    $col_voID->addValue($voID);
                }

                $insert = SQLInsert::Table("product_variants");
                $insert->setColumn($col_prodID);
                $insert->setColumn($col_voID);

                $db->query($insert)->free();

            }
            else {

                //clear all variants
                $delete = SQLDelete::Table("product_variants");
                $delete->where()->add("prodID", $this->prodID);

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

        $select = SQLSelect::Table(" product_variants pv JOIN variant_options vo ON vo.voID = pv.voID ");
        $select->set("vo.voID", "vo.option_name", "vo.option_value", "vo.parentID");
        $select->where()->add("pv.prodID", $this->prodID);

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

$menu = array(
//    new MenuItem("Inventory", "inventory/list.php", "list"),
);

$cmp = new BeanListPage();
$req = new BeanKeyCondition(new ProductsBean(),  "../list.php", array("product_name"));

$handler = new VariantPriceFormResponder((int)$req->getValue());

$cmp->getPage()->setPageMenu($menu);

$cmp->getPage()->setName(tr("Product Variants").": ".$req->getData("product_name"));

$form = new ProductVariantsInputForm($req->getValue());

$rend = new FormRenderer($form);
$proc = new ProductVariantsProcessor($req->getValue());
$proc->loadFormData($form);

$proc->process($form);
if ($proc->getMessage()) {
    Session::SetAlert($proc->getMessage());
}

$closure = function(ClosureComponent $cmp) use($rend) {

    echo "<div class='Caption'>".tr("Варианти за този продукт - избираеми преди покупка")."</div>";
    echo "<br>";

    $rend->render();

    echo "<HR>";
};
$cmp->items()->append(new ClosureComponent($closure));


$bean = new ProductVariantsBean();
$query = $bean->queryProduct((int)$req->getID());

$cmp->setIterator($query);

$cmp->setListFields(array("pvID"=>"pvID", "option_name"=>"Option Name", "option_value"=>"Option Value", "price"));


$view = $cmp->initView();
$col = $cmp->viewItemActions();
$col->clear();

$col->append( new Action("Photo Gallery", "gallery/list.php", array(new DataParameter("pvID"))));

$cmp->getPage()->getActions()->removeByAction(SparkAdminPage::ACTION_ADD);
$cmp->render();
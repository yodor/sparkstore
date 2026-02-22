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
    protected $voptions;

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
        $query->select->where()->add("pclsID" , " null ", " is ");
        $query->select->where()->add("parentID" , " null ", " is ");
        $query->select->where()->add("prodID" , " null ", " is ");
        $query->select->where()->add("option_value" , " null ", " is ");

        $num = $query->exec();
        while ($result = $query->nextResult()) {
            $voID = $result->get("voID");
            $option_name = $result->get("option_name");

            $input = new DataInput("voID_$voID", $option_name, 0);
            $this->createInputIterator($input, $voID);
            $this->addInput($input, $group_normal);
        }

        ///options from product class
        $pclsID = (int)$product["pclsID"];
        if ($pclsID>0) {
            $query = $this->voptions->queryFull();
            $query->select->where()->add("pclsID" , $pclsID);
            $query->select->where()->add("parentID" , " null ", " is ");
            $query->select->where()->add("option_value" , " null ", " is ");
            $query->select->where()->add("prodID" , " null ", " is ");

            $num = $query->exec();
            while ($result = $query->nextResult()) {
                $voID = $result->get("voID");
                $option_name = $result->get("option_name");

                $input = new DataInput("voID_$voID", $option_name, 0);
                $this->createInputIterator($input, $voID);
                $this->addInput($input, $group_class);
            }
        }

        ///options for this product
        $query = $this->voptions->queryFull();
        $query->select->where()->add("prodID" , $prodID);
        $query->select->where()->add("parentID" , " null ", " is ");
        $query->select->where()->add("option_value" , " null ", " is ");
        $query->select->where()->add("pclsID" , " null ", " is ");

        $num = $query->exec();
        while ($result = $query->nextResult()) {
            $voID = $result->get("voID");
            $option_name = $result->get("option_name");

            $input = new DataInput("voID_$voID", $option_name, 0);
            $this->createInputIterator($input, $voID);
            $this->addInput($input, $group_product);
        }


    }

    protected function createInputIterator(DataInput $input, int $parentID)
    {
        $validator = new EmptyValueValidator();
        $input->setValidator($validator);

        $query_parameters = $this->voptions->queryFull();
        $query_parameters->select->where()->add("parentID" , $parentID);

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
    protected $prodID = -1;


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


    protected function storeFormData(InputForm $form)
    {

        $db = DBConnections::Open();



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

                $id_list = implode(",", $posted_voIDs);
                $db->query("DELETE FROM product_variants WHERE prodID={$this->prodID} AND voID NOT IN ($id_list) ");

                //insert non-existing IDs
                $values_list = array();
                foreach ($posted_voIDs as $idx => $voID) {
                    $values_list[] = "({$this->prodID}, $voID)";
                }
                $values_list = implode(",", $values_list);

                $db->query("INSERT IGNORE INTO product_variants (prodID, voID) VALUES $values_list ");

            }
            else {
                //clear all variants
                $db->query("DELETE FROM product_variants WHERE prodID={$this->prodID}");
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
        $select->fields()->set("vo.voID", "vo.option_name", "vo.option_value", "vo.parentID");
        $select->from = " product_variants pv JOIN variant_options vo ON vo.voID = pv.voID ";
        $select->where()->add("pv.prodID", $this->prodID);

        $query = new SQLQuery($select, "pvID");
        $num = $query->exec();

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
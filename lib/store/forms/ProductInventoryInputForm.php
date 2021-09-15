<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("input/renderers/ArrayField.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ClassAttributesBean.php");
include_once("store/beans/ProductColorsBean.php");
include_once("store/beans/StoreSizesBean.php");
include_once("store/beans/InventoryAttributeValuesBean.php");

include_once("store/input/renderers/ClassAttributeField.php");
include_once("store/input/renderers/SourceRelatedField.php");
include_once("store/beans/ProductCategoriesBean.php");

class ProductInventoryInputForm extends InputForm
{

    protected $prodID = -1;
    protected $product = array();

    public function __construct(bool $is_multi_add=false)
    {

        parent::__construct();

//        $type = DataInputFactory::SELECT;
//        if ($is_multi_add) {
//            $type = DataInputFactory::SELECT_MULTI;
//        }
//        $field = DataInputFactory::Create($type, "pclrID", "Цветова схема", 0);
//        $colors = new ProductColorsBean();
//        $field->getRenderer()->setIterator($colors->query("pclrID", "color"));
//
//        $field->getRenderer()->getItemRenderer()->setValueKey("pclrID");
//        $field->getRenderer()->getItemRenderer()->setLabelKey("color");
//
//        $field->getProcessor()->renderer_source_copy_fields = array("color");
//
//        $this->addInput($field);
//
//        $field = DataInputFactory::Create($type, "size_value", "Оразмеряване", 0);
//        $sizes = new StoreSizesBean();
//        $qry_sizes = $sizes->query("pszID", "size_value");
//        $qry_sizes->select->order_by = " position ASC ";
//        $field->getRenderer()->setIterator($qry_sizes);
//        $field->getRenderer()->getItemRenderer()->setValueKey("size_value");
//        $field->getRenderer()->getItemRenderer()->setLabelKey("size_value");
//
//        //$field->getRenderer()->addon_content = "<a class='Action' action='inline-new' href='../../sizes/add.php'>" . tr("Нов код за оразмеряване") . "</a>";
//
//        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "stock_amount", "Стокова наличност", 1);
        //default stock amount
        $field->setValue(1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::HIDDEN, "weight", "Тегло", 0);
        $this->addInput($field);


        $field = DataInputFactory::Create(DataInputFactory::HIDDEN, "price", "Продажна цена", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::HIDDEN, "buy_price", "Покупна цена", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::HIDDEN, "promo_price", "Стара цена", 0);
        $this->addInput($field);


        //1. input is taking array of values (ArrayDataInput)
        //2. renderer is drawing single element with many items (DataSourceField)
        $field = new ArrayDataInput("value", "Атрибути", 0);

        $field->source_label_visible = TRUE;

        $field->getProcessor()->process_datasource_foreign_keys = TRUE;

        $bean1 = new InventoryAttributeValuesBean();
        $field->getProcessor()->setTransactBean($bean1);

        $rend = new IteratorRelatedField($field);

        $bean = new ClassAttributesBean();
        $rend->setIterator($bean->queryFull());

        $rend->getItemRenderer()->setValueKey("value");
        $rend->getItemRenderer()->setLabelKey("attribute_name");

        $this->addInput($field);
    }

    public function setProductID(int $prodID)
    {
        $this->prodID = (int)$prodID;

//        $this->getInput("pclrID")->getRenderer()->getIterator()->select->where()->add("prodID", $this->prodID);
//
//        $this->getInput("pclrID")->getRenderer()->addon_content = "<a class='Action' action='inline-new' href='../color_gallery/add.php?prodID={$this->prodID}'>" . tr("Нова цветова схема") . "</a>";

        $prods = new ProductsBean();
        $this->product = $prods->getByID($this->prodID);

        $rend = $this->getInput("value")->getRenderer();

        $rend->setCaption(tr("Продуктов клас") . ": " . $this->product["class_name"]);

        $iterator = $rend->getIterator();
        if (!($iterator instanceof SQLQuery))throw new Exception("Incorrect iterator");

        $sel = $iterator->select;
        $sel->fields()->reset();
        $sel->fields()->set("ca.*", "attr.unit AS attribute_unit", "attr.type AS attribute_type");
        $sel->from = $rend->getIterator()->name() . " ca LEFT JOIN attributes attr ON attr.name = ca.attribute_name ";
        $sel->where()->add("ca.class_name", "'{$this->product["class_name"]}'");

        //echo $sel->getSQL();
        //debug($sel->getSQL());

        $this->getInput("price")->setValue($this->product["price"]);
        $this->getInput("buy_price")->setValue($this->product["buy_price"]);
        $this->getInput("promo_price")->setValue($this->product["promo_price"]);
        $this->getInput("weight")->setValue($this->product["weight"]);


    }

    public function loadBeanData(int $editID, DBTableBean $bean)
    {

        $item_row = parent::loadBeanData($editID, $bean);

        $rend = $this->getInput("value")->getRenderer();

        $iterator = $rend->getIterator();
        if (!($iterator instanceof SQLQuery))throw new Exception("Incorrect iterator");

        $sel = $iterator->select;

        $sel->fields()->reset();
        $sel->fields()->set(" ca.*", "iav.value", "attr.unit AS attribute_unit", "attr.type AS attribute_type");

        $sel->from = $rend->getIterator()->name() . " ca LEFT
        JOIN inventory_attribute_values iav ON iav.caID = ca.caID AND iav.piID='$editID' LEFT
        JOIN attributes attr ON attr.name = ca.attribute_name ";

        $sel->where()->add("ca.class_name", "'{$this->product["class_name"]}'");



    }

    public function loadPostData(array $arr)
    {
        parent::loadPostData($arr);

    }
}

?>

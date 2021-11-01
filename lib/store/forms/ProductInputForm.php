<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

include_once("store/beans/BrandsBean.php");
include_once("store/beans/SectionsBean.php");

include_once("store/beans/ProductClassesBean.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/ProductFeaturesBean.php");
include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ProductAttributeValuesBean.php");
include_once("store/input/renderers/SourceRelatedField.php");
include_once("store/beans/ClassAttributesBean.php");

class ProductInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

//        $field = DataInputFactory::Create(DataInputFactory::SELECT, "section", "Section", 0);
//        $rend = $field->getRenderer();
//        $sb = new SectionsBean();

//        $rend->setIterator($sb->query($sb->key(),"section_title"));
//        $rend->getItemRenderer()->setValueKey("section_title");
//        $rend->getItemRenderer()->setLabelKey("section_title");
//        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::NESTED_SELECT, "catID", "Category", 1);
        $bean1 = new ProductCategoriesBean();
        $rend = $field->getRenderer();

        $rend->setIterator(new SQLQuery($bean1->selectTree(array("category_name")), $bean1->key(), $bean1->getTableName()));
        $rend->getItemRenderer()->setValueKey("catID");
        $rend->getItemRenderer()->setLabelKey("category_name");

        $this->addInput($field);

//        $field = DataInputFactory::Create(DataInputFactory::TEXT_SEARCH, "brand_name", "Author", 1);
//        $rend = $field->getRenderer();
//        $brands = new BrandsBean();
//
//        $rend->setIterator($brands->query($brands->key(), "brand_name"));
//        $rend->getItemRenderer()->setValueKey("brand_name");
//        $rend->getItemRenderer()->setLabelKey("brand_name");
//        $this->addInput($field);

//        $field = DataInputFactory::Create(DataInputFactory::SELECT, "class_name", "Product Class", 0);
//        $rend = $field->getRenderer();
//        $pcb = new ProductClassesBean();
//        $rend->setIterator($pcb->query($pcb->key(), "class_name"));
//        $rend->getItemRenderer()->setValueKey("class_name");
//        $rend->getItemRenderer()->setLabelKey("class_name");
//        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "product_name", "Заглавие", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "product_description", "Подзаглавие", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "price", "Продажна цена", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "buy_price", "Покупна цена", 1);
        $field->setValue(0.0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "promo_price", "Промо цена", 1);
        $field->setValue(0.0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::CHECKBOX, "visible", "Видим (в продажба)", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::SESSION_IMAGE, "photo", "Снимки", 1);
        $pphotos = new ProductPhotosBean();

        $field->getProcessor()->setTransactBean($pphotos);
        $field->getProcessor()->setTransactBeanItemLimit(10);
        //$field->getRenderer()->setIterator($pphotos->queryFull());
        $this->addInput($field);

        // 	$field = DataInputFactory::CreateField(DataInputFactory::CHECKBOX, "promotion", "Promotion", 0);
        // 	$this->addInput($field);


        $field = DataInputFactory::Create(DataInputFactory::MCE_TEXTAREA, "long_description", "Описание на продукта", 1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "keywords", "Ключови думи", 0);
        $this->addInput($field);

        $field1 = new ArrayDataInput("feature", "Особености", 0);
        $field1->source_label_visible = TRUE;

        $field1->setValidator(new EmptyValueValidator());
        $proc = new InputProcessor($field1);

        $renderer = new TextField($field1);
        new ArrayField($renderer);

        $features = new ProductFeaturesBean();
        $field1->getProcessor()->setTransactBean($features);

        $renderer->setIterator($features->queryFull());

        $this->addInput($field1);


        /////
        ///
        //1. input is taking array of values (ArrayDataInput)
        //2. renderer is drawing single element with many items (DataSourceField)
        $field = new ArrayDataInput("value", "Атрибути", 0);

        $field->source_label_visible = TRUE;

        $field->getProcessor()->process_datasource_foreign_keys = TRUE;

        $bean1 = new ProductAttributeValuesBean();
        $field->getProcessor()->setTransactBean($bean1);

        $rend = new IteratorRelatedField($field);

        $bean = new ClassAttributesBean();
        $rend->setIterator($bean->queryFull());

        $rend->getItemRenderer()->setValueKey("value");
        $rend->getItemRenderer()->setLabelKey("attribute_name");

        $this->addInput($field);
    }

    public function loadBeanData($editID, DBTableBean $bean)
    {

        $item_row = parent::loadBeanData($editID, $bean);


        //       $renderer = $this->getInput("value")->getRenderer();
        //       $renderer->setCategoryID($this->getInput("catID")->getValue());
        //       $renderer->setProductID($editID);

        $rend = $this->getInput("value")->getRenderer();

        $iterator = $rend->getIterator();
        if (!($iterator instanceof SQLQuery))throw new Exception("Incorrect iterator");

        $sel = $iterator->select;

        $sel->fields()->reset();
        $sel->fields()->set(" ca.*", "pav.value", "attr.unit AS attribute_unit", "attr.type AS attribute_type");

        $sel->from = $rend->getIterator()->name() . " ca LEFT
        JOIN product_attribute_values pav ON pav.caID = ca.caID AND pav.prodID='$editID' LEFT
        JOIN attributes attr ON attr.name = ca.attribute_name ";

//        echo $sel->getSQL();

        $class_name = "Продукти";

        //$class_name = $item_row["class_name"];
        $sel->where()->add("ca.class_name", "'$class_name'");

    }

    public function loadPostData(array $arr)
    {
        parent::loadPostData($arr);

        //       $renderer = $this->getInput("value")->getRenderer();
        //       $renderer->setCategoryID($arr["catID"]);

    }
}

?>

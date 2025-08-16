<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

include_once("store/beans/BrandsBean.php");
include_once("store/beans/SectionsBean.php");
include_once("store/beans/ProductSectionsBean.php");

include_once("store/beans/ProductClassesBean.php");
include_once("store/beans/ProductClassAttributesBean.php");
include_once("store/beans/ProductClassAttributeValuesBean.php");

include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/ProductFeaturesBean.php");
include_once("store/beans/ProductPhotosBean.php");

include_once("store/input/renderers/ClassAttributeField.php");
include_once("input/validators/NumericValidator.php");

class ProductInputFormBase extends InputForm
{

    public function __construct()
    {
        parent::__construct();


        $field = DataInputFactory::Create(DataInputFactory::NESTED_SELECT, "catID", "Category", 1);
        $bean1 = new ProductCategoriesBean();
        $rend = $field->getRenderer();

        $rend->setIterator(new SQLQuery($bean1->selectTree(array("category_name")), $bean1->key(), $bean1->getTableName()));
        $rend->getItemRenderer()->setValueKey("catID");
        $rend->getItemRenderer()->setLabelKey("category_name");

        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::SELECT, "brand_name", "Brand", 1);
        $rend = $field->getRenderer();
        $brands = new BrandsBean();

        $rend->setIterator($brands->query($brands->key(), "brand_name"));
        $rend->getItemRenderer()->setValueKey("brand_name");
        $rend->getItemRenderer()->setLabelKey("brand_name");
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "product_name", "Заглавие / SEO заглавие", 1);
        $field->getRenderer()->input()->setAttribute("size", "50em");
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::MCE_TEXTAREA, "product_description", "Описание", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "seo_description", "SEO описание (до 150 символа)", 0);
        $field->enableTranslator(true);
        $field->getRenderer()->input()->setAttribute("maxLength","150");
        $field->getRenderer()->input()->setAttribute("rows","3");
        $field->getRenderer()->input()->setAttribute("cols","60");
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "price", "Продажна цена", 1);
        $field->setValidator(new NumericValidator(false,false));
        $field->setValue(0.0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "promo_price", "Промо цена", 1);
        $field->setValidator(new NumericValidator(true,false));
        $field->setValue(0.0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "stock_amount", "Стокова наличност", 1);
        //default stock amount
        $field->setValidator(new NumericValidator(true,false));
        $field->setValue(1);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::CHECKBOX, "visible", "Видим (в продажба)", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::SESSION_IMAGE, "photo", "Снимки", 1);
        $pphotos = new ProductPhotosBean();
        $field->getProcessor()->setTransactBean($pphotos);
        $field->getProcessor()->setTransactBeanItemLimit(20);

        $this->addInput($field);


        //
        $field = new ArrayDataInput("secID", "Section", 0);
        $proc = new InputProcessor($field);
        $proc->transact_bean_skip_empty_values = true;
        $proc->merge_with_target_loaded = false;

        $renderer = new CheckField($field);
        //$renderer = new SelectMultipleField($field);

        $rend = $field->getRenderer();
        $sb = new SectionsBean();
        $rend->setIterator($sb->query($sb->key(),"section_title"));
        $rend->getItemRenderer()->setValueKey($sb->key());
        $rend->getItemRenderer()->setLabelKey("section_title");

        $product_sections = new ProductSectionsBean();
        $field->getProcessor()->setTransactBean($product_sections);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::SELECT, "pclsID", "Product Class", 0);
        $rend = $field->getRenderer();
        $pcb = new ProductClassesBean();

        $rend->setIterator($pcb->query($pcb->key(), "class_name"));
        $rend->getItemRenderer()->setValueKey($pcb->key());
        $rend->getItemRenderer()->setLabelKey("class_name");

        $this->addInput($field);


        $this->addInput(ClassAttributeField::Create("value", "Атрибути", 0));


        //
        $field1 = new ArrayDataInput("feature", "Особености", 0);
        $field1->source_label_visible = TRUE;

        $field1->setValidator(new EmptyValueValidator());
        $proc = new InputProcessor($field1);
        $proc->transact_bean_skip_empty_values = true;
        $proc->merge_with_target_loaded = false;

        $renderer = new TextField($field1);
        new ArrayField($renderer);

        $features = new ProductFeaturesBean();
        $field1->getProcessor()->setTransactBean($features);

        $renderer->setIterator($features->queryFull());

        $this->addInput($field1);
        //

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "keywords", "Ключови думи", 0);
        $field->getRenderer()->input()->setAttribute("rows", 5);
        $field->getRenderer()->input()->setAttribute("cols", 80);
        $this->addInput($field);

    }

    public function validate(): void
    {
        parent::validate();

        $price = $this->getInput("price")->getValue();
        $promo_price = $this->getInput("promo_price")->getValue();
        $price = floatval($price);
        $promo_price = floatVal($promo_price);
        if ($promo_price>=$price && $promo_price>0) {
            throw new Exception("'Promo price' must be smaller than 'Sell price'");
        }
    }

    public function loadBeanData($editID, DBTableBean $bean): array
    {
        $result = parent::loadBeanData($editID, $bean);
        $renderer = $this->getInput("value")->getRenderer();
        if ($renderer instanceof ClassAttributeField) {

            $renderer->setProductID((int)$editID);
            $renderer->setClassID((int)$result["pclsID"]);

        }
        return $result;
    }

    public function loadPostData(array $data) : void
    {

        parent::loadPostData($data);

        $renderer = $this->getInput("value")->getRenderer();
        if ($renderer instanceof ClassAttributeField) {
            $renderer->setClassID((int)$data["pclsID"]);
            debug("Setting PCLSID: ". (int)$data["pclsID"]);
//            if (isset($arr["prodID"])) {
//                $renderer->setProductID((int)$arr["prodID"]);
//            }
        }
    }
}

?>

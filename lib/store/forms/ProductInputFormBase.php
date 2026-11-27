<?php
include_once("forms/InputForm.php");
include_once("forms/SEOInputGroup.php");
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
include_once("store/utils/CatalogParts.php");

class ProductInputFormBase extends InputForm
{

    public function __construct()
    {
        parent::__construct();


        $field = DataInputFactory::Create(InputType::NESTED_SELECT, "catID", tr("Category"), 1);
        $bean1 = new ProductCategoriesBean();
        $rend = $field->getRenderer();

        $rend->setIterator(new SelectQuery($bean1->selectTree(array("category_name")), $bean1->key(), $bean1->table()));
        $rend->getItemRenderer()->setValueKey("catID");
        $rend->getItemRenderer()->setLabelKey("category_name");

        $this->addInput($field);

        //the inputs of the optional catalog parts (CatalogParts) - brand, sections, class with its attributes
        if (CatalogParts::Enabled(CatalogParts::BRANDS)) {
            $field = DataInputFactory::Create(InputType::SELECT, "brand_name", tr("Brand"), 1);
            $rend = $field->getRenderer();
            $brands = new BrandsBean();

            $rend->setIterator($brands->query($brands->key(), "brand_name"));
            $rend->getItemRenderer()->setValueKey("brand_name");
            $rend->getItemRenderer()->setLabelKey("brand_name");
            $this->addInput($field);
        }

        $field = DataInputFactory::Create(InputType::TEXT, "product_name", "Title", 1);
        $field->getRenderer()->input()->setAttribute("size", "50em");
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::MCE_TEXTAREA, "product_description", "Description", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "conversionID", "Conversion Tag", 0);
        $field->getRenderer()->input()->setAttribute("size", "50em");
        $field->getRenderer()->input()->setAttribute("placeholder", "AW-CONVERSION_ID/CONVERSION_LABEL");
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "price", "Sell Price", 1);
        $field->setValidator(new NumericValidator(false,false));
        $field->setValue(0.0);
        $field->getRenderer()->getAddonContainer()->items()->append(new TextComponent(Spark::Get(StoreConfig::DEFAULT_CURRENCY_SYMBOL), ""));

        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "promo_price", "Promo Price", 1);
        $field->setValidator(new NumericValidator(true,false));
        $field->setValue(0.0);
        $field->getRenderer()->getAddonContainer()->items()->append(new TextComponent(Spark::Get(StoreConfig::DEFAULT_CURRENCY_SYMBOL), ""));

        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "stock_amount", "Stock Amount", 1);
        //default stock amount
        $field->setValidator(new NumericValidator(true,false));
        $field->setValue(1);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::CHECKBOX, "visible", "Visible (on sale)", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::SESSION_IMAGE, "photo", "Photos", 1);
        $pphotos = new ProductPhotosBean();
        $field->getProcessor()->setTransactBean($pphotos);
        $field->getProcessor()->setTransactBeanItemLimit(20);

        $this->addInput($field);


        if (CatalogParts::Enabled(CatalogParts::SECTIONS)) {
            $field = new ArrayDataInput("secID", tr("Section"), 0);
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
        }

        if (CatalogParts::Enabled(CatalogParts::CLASSES)) {
            $field = DataInputFactory::Create(InputType::RADIO, "pclsID", tr("Product Class"), 0);
            $rend = $field->getRenderer();
            $pcb = new ProductClassesBean();

            $rend->setIterator($pcb->query($pcb->key(), "class_name"));
            $rend->getItemRenderer()->setValueKey($pcb->key());
            $rend->getItemRenderer()->setLabelKey("class_name");

            $rend->setDefaultOption("No class", "null");


            $this->addInput($field);


            $this->addInput(ClassAttributeField::Create("value", "Attributes", 0));
        }

        //
        $field1 = new ArrayDataInput("feature", "Features", 0);
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

        SEOInputGroup::AddTo($this);
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
        if (!$this->haveInput("value")) return $result;
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

        if (!$this->haveInput("value")) return;
        $renderer = $this->getInput("value")->getRenderer();
        if ($renderer instanceof ClassAttributeField) {
            if (isset($data["pclsID"])) {
                $pclsID = (int)$data["pclsID"];
                $renderer->setClassID($pclsID);
                Debug::ErrorLog("Setting PCLSID: ". (int)$data["pclsID"]);
            }


        }
    }
}
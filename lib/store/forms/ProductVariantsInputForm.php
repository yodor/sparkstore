<?php
include_once("forms/InputForm.php");
include_once("store/beans/VariantOptionsBean.php");
include_once("store/beans/ProductsBean.php");
include_once("input/renderers/CheckField.php");
include_once("input/validators/EmptyValueValidator.php");
include_once("input/processors/InputProcessor.php");

/**
 * Variant options of one product - one check group per option (base options, options of the
 * product class, options of the product). Used with ProductVariantsProcessor.
 */
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
        $query->stmt->where()->expression("pclsID IS NULL");
        $query->stmt->where()->expression("parentID IS NULL");
        $query->stmt->where()->expression("prodID IS NULL");
        $query->stmt->where()->expression("option_value IS NULL");

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
            $query->stmt->where()->match("pclsID" , $pclsID);
            $query->stmt->where()->expression("parentID IS NULL");
            $query->stmt->where()->expression("option_value IS NULL");
            $query->stmt->where()->expression("prodID IS NULL");

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
        $query->stmt->where()->match("prodID" , $prodID);
        $query->stmt->where()->expression("parentID IS NULL");
        $query->stmt->where()->expression("option_value IS NULL");
        $query->stmt->where()->expression("pclsID IS NULL");

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
        $query_parameters->stmt->where()->match("parentID" , $parentID);

        $cf3 = new CheckField($input);
        $cf3->setArrayKeyFieldName("voID");
        $cf3->setIterator($query_parameters);
        $cf3->getItemRenderer()->setValueKey("option_value");
        $cf3->getItemRenderer()->setLabelKey("option_value");
        new InputProcessor($input);
    }

}

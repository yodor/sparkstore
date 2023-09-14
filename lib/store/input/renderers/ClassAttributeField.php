<?php
include_once("input/renderers/DataIteratorField.php");
include_once("components/renderers/items/DataIteratorItem.php");
include_once("store/beans/ProductClassAttributesBean.php");
include_once("store/beans/ProductClassAttributeValuesBean.php");

class ClassAttributeItem extends DataIteratorItem
{

    public function renderImpl()
    {

        echo "<label data='attribute_name'>" . $this->label . "</label>";

        echo "<input data='attribute_value' type='text' value='{$this->value}' name='{$this->name}'>";

        //value foreign key
        echo "<input data='foreign_key' type='hidden' name='fk_{$this->name}' value='pcaID:{$this->data["pcaID"]}'>";
    }

}

class ClassAttributeFieldResponder extends JSONResponder
{
    protected $classID = -1;
    protected $prodID = -1;
    protected $field;

    public function __construct(ClassAttributeField $field)
    {
        parent::__construct("ClassAttributeField");
        $this->field = $field;
    }

    public function parseParams()
    {
        parent::parseParams();

        if (isset($_GET["classID"])) {
            $this->classID = (int)$_GET["classID"];
        }
        if (isset($_GET["prodID"])) {
            $this->prodID = (int)$_GET["prodID"];
        }
    }

    public function _render(JSONResponse $req)
    {

        $this->field->setClassID($this->classID);
        $this->field->setProductID($this->prodID);

        $this->field->renderImpl();

    }
}

class ClassAttributeField extends DataIteratorField
{

    protected $classID = -1;
    protected $prodID = -1;

    public static function Create(string $name, string $label, bool $required) : ArrayDataInput
    {
        $field = new ArrayDataInput($name, $label, $required);

        $field->source_label_visible = TRUE;
        //try merge even if posted count is different
        $field->getProcessor()->merge_with_target_loaded = TRUE;
        //check fk_$name values posted
        $field->getProcessor()->process_datasource_foreign_keys = TRUE;
        //do not transact empty string during insert
        //instead of update the value will be deleted
        $field->getProcessor()->transact_bean_skip_empty_values = TRUE;

        $bean1 = new ProductClassAttributeValuesBean();
        $field->getProcessor()->setTransactBean($bean1);

        $rend = new ClassAttributeField($field);
        return $field;
    }

    public function __construct(DataInput $input)
    {
        parent::__construct($input);
        $this->setItemRenderer(new ClassAttributeItem());

        $this->getItemRenderer()->setValueKey("pcaID");
        $this->getItemRenderer()->setLabelKey("name");

        $responder = new ClassAttributeFieldResponder($this);

        $this->updateIterator();
    }

    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = STORE_LOCAL . "/css/ClassAttributeField.css";
        return $arr;
    }

    public function setClassID(int $classID)
    {
        $this->classID = $classID;
        $this->updateIterator();
    }

    public function updateIterator()
    {
        $sel = new SQLSelect();

        $sel->fields()->set("pca.pcaID", "attr.name");
        $sel->fields()->setExpression("(SELECT pcav.value FROM product_class_attribute_values pcav WHERE pcav.pcaID=pca.pcaID AND pcav.prodID={$this->prodID})", "value");
        $sel->fields()->setExpression("(SELECT pcav.pcavID FROM product_class_attribute_values pcav WHERE pcav.pcaID=pca.pcaID AND pcav.prodID={$this->prodID})", "pcavID");

        $sel->from = "`product_class_attributes` pca JOIN `attributes` attr ON attr.attrID=pca.attrID";

        $sel->order_by = " pcaID ASC ";

        if ($this->classID>0) {
            $sel->where()->add("pca.pclsID", $this->classID);
        }

        $this->setIterator(new SQLQuery($sel, "pcaID"));
    }

    public function setProductID(int $prodID)
    {
        $this->prodID = $prodID;
        $this->updateIterator();
    }

    public function renderImpl()
    {

        if ($this->classID < 1) {

            echo tr("Select product class first");
            return;
        }

        parent::renderImpl();

    }

    protected function renderItems()
    {

        if ($this->iterator->count() < 1) {
            echo tr("No optional attributes");
            return;
        }

        $this->getItemRenderer()->setValueKey($this->input->getName());

        parent::renderItems();
    }

    public function finishRender()
    {
        parent::finishRender();
        ?>
        <script type='text/javascript'>
            onPageLoad(function () {
                console.log("Adding class changed handler");

                $("[name='pclsID']").on("change", function () {
                    console.log("Product Class Changed");

                    let classID = $(this).val();

                    let req = new JSONRequest();
                    req.setResponder("ClassAttributeField");
                    req.setFunction("render");
                    req.setParameter("classID", classID);
                    req.setParameter("prodID", <?php echo $this->prodID;?>);

                    req.onSuccess = function(request_result) {
                        let result = request_result.json_result;
                        let html = result.contents;
                        $(".ClassAttributeField[field='<?php echo $this->input->getName();?>']").html(html);
                        dispatchEvent(new Event('load'));
                    };

                    req.start();

                });
            });
        </script>
        <?php
    }

}

?>
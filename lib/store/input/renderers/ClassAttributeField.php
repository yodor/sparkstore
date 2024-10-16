<?php
include_once("input/renderers/DataIteratorField.php");
include_once("components/renderers/items/DataIteratorItem.php");
include_once("store/beans/ProductClassAttributesBean.php");
include_once("store/beans/ProductClassAttributeValuesBean.php");

class ClassAttributeItem extends DataIteratorItem
{

    protected Component $attributeName;
    protected Input $attributeValue;
    protected Input $foreginKey;

    public function __construct() {
        parent::__construct();

        $this->setClassName("ClassAttributeItem");

        $this->attributeName = new Component(false);
        $this->attributeName->setTagName("LABEL");
        $this->attributeName->setAttribute("data", "attribute_name");
        $this->items()->append($this->attributeName);

        $this->attributeValue = new Input();
        $this->attributeValue->setType("text");
        $this->attributeValue->setAttribute("data", "attribute_value");
        $this->attributeValue->setAttribute("placeholder", tr("input value ..."));
        $this->items()->append($this->attributeValue);

        $this->foreginKey = new Input();
        $this->foreginKey->setType("hidden");
        $this->foreginKey->setAttribute("data", "foreign_key");

        $this->items()->append($this->foreginKey);

    }

    protected function getInput() : Component
    {
        return $this->attributeValue;
    }

    public function setData(array $data): void
    {
        parent::setData($data);
        $this->attributeName->setContents($this->label);


    }

    protected function processAttributes(): void
    {
        parent::processAttributes();
        $this->removeAttribute("name");

        $this->attributeValue->setAttribute("value", $this->value);



        if ($this->parent instanceof DataIteratorField) {

            if (!$this->value) {
                debug("Request Data: ",$_REQUEST);
                $dataInput = $this->parent->getDataInput();
                $inputValue = $dataInput->getValue();
                //search post data for value
                if (isset($_REQUEST["fk_" . $dataInput->getName()])) {
                    if (isset($inputValue[$this->position])) {
                        $this->value = $inputValue[$this->position];
                        $this->attributeValue->setAttribute("value", $this->value);
                    }
                }
            }

            //follow main input name and prefix with fk_
            $this->foreginKey->setName("fk_" . $this->attributeValue->getName());
            //already set in id ?
            $prKey = $this->parent->getIterator()->key();
            $this->foreginKey->setValue($prKey . ":" . $this->getID());

        }
    }


}

class ClassAttributeFieldResponder extends JSONResponder
{
    protected int $classID = -1;
    protected int $prodID = -1;
    protected ClassAttributeField $field;

    public function __construct(ClassAttributeField $field)
    {
        parent::__construct();
        $this->field = $field;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function parseParams() : void
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

        $this->field->render();
    }
}

class ClassAttributeField extends DataIteratorField
{

    protected int $classID = -1;
    protected int $prodID = -1;

    public static function Create(string $name, string $label, bool $required) : ArrayDataInput
    {
        $dataInput = new ArrayDataInput($name, $label, $required);

        $dataInput->source_label_visible = TRUE;
        //try merge even if posted count is different
        $dataInput->getProcessor()->merge_with_target_loaded = FALSE;
        //check fk_$name values posted
        $dataInput->getProcessor()->process_datasource_foreign_keys = TRUE;
        //do not transact empty string during insert
        //instead of update the value will be deleted
        $dataInput->getProcessor()->transact_bean_skip_empty_values = TRUE;

        $bean1 = new ProductClassAttributeValuesBean();
        $dataInput->getProcessor()->setTransactBean($bean1);

        $rend = new ClassAttributeField($dataInput);
        return $dataInput;
    }

    public function __construct(DataInput $input)
    {
        parent::__construct($input);

        $this->setItemRenderer(new ClassAttributeItem());

        //'input->getName()' should match 'value' column from iterator
        $this->getItemRenderer()->setValueKey($input->getName());

        //'name' column from iterator
        $this->getItemRenderer()->setLabelKey("name");

        $responder = new ClassAttributeFieldResponder($this);

        //set initial iterator - without pclsID set will list all attributes for all classes
        //$this->updateIterator();
    }

    protected function processAttributes(): void
    {
        parent::processAttributes();
        $this->updateIterator();

    }

    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = STORE_LOCAL . "/css/ClassAttributeField.css";
        return $arr;
    }

    public function setClassID(int $classID) : void
    {
        $this->classID = $classID;
    }
    public function setProductID(int $prodID) : void
    {
        $this->prodID = $prodID;
    }

    public function updateIterator() : void
    {
        $sel = new SQLSelect();

        $sel->fields()->set("pca.pcaID", "attr.name");
        $sel->fields()->setExpression("(SELECT pcav.value FROM product_class_attribute_values pcav WHERE pcav.pcaID=pca.pcaID AND pcav.prodID={$this->prodID})", $this->dataInput->getName());
        $sel->fields()->setExpression("(SELECT pcav.pcavID FROM product_class_attribute_values pcav WHERE pcav.pcaID=pca.pcaID AND pcav.prodID={$this->prodID})", "pcavID");

        $sel->from = "product_class_attributes pca JOIN attributes attr ON attr.attrID=pca.attrID";

        $sel->order_by = " pcaID ASC ";

        $sel->where()->add("pca.pclsID", $this->classID);

        $this->setIterator(new SQLQuery($sel, "pcaID"));
    }



    public function renderImpl()
    {

        if ($this->classID < 1) {

            echo tr("Select product class first");
            return;
        }

        parent::renderImpl();

    }


    public function render()
    {
        parent::render();
        ?>
        <script type='text/javascript'>
            onPageLoad(function () {
                console.log("Adding class changed handler");

                let input = document.querySelector("[name='pclsID']");
                input.addEventListener("change", (event)=>{

                    let req = new JSONRequest();
                    req.setResponder("ClassAttributeFieldResponder");
                    req.setFunction("render");
                    req.setParameter("classID", input.value);
                    req.setParameter("prodID", <?php echo $this->prodID;?>);

                    req.onSuccess = function(result) {

                        const field = document.querySelector(".InputComponent[field='<?php echo $this->dataInput->getName();?>'] .ClassAttributeField");
                        //no scripts will be parsed or added
                        field.innerHTML = result.response.contents;

                        const event = new SparkEvent(SparkEvent.DOM_UPDATED);
                        event.source = field;
                        document.dispatchEvent(event);
                    };

                    req.start();

                });

            });
        </script>
        <?php
    }

}

?>

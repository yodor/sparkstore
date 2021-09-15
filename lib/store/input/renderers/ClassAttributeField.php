<?php
include_once("input/renderers/DataIteratorField.php");
include_once("components/renderers/items/DataIteratorItem.php");
include_once("store/beans/ClassAttributesBean.php");

class ClassAttributeItem extends DataIteratorItem
{

    public function renderImpl()
    {

        echo "<label data='attribute_name'>" . $this->label . "</label>";

        echo "<input data='attribute_value' type='text' value='{$this->value}' name='{$this->name}'>";

        echo "<input data='foreign_key' type='hidden' name='fk_{$this->name}' value='caID:{$this->id}'>";

        echo "<label data='attribute_unit'>" . $this->data["unit"] . "</label>";
    }

}

class ClassAttributeFieldResponder extends JSONResponder
{
    protected $catID = -1;
    protected $prodID = -1;

    public function __construct()
    {
        parent::__construct("ClassAttributeField");
    }

    public function parseParams()
    {
        parent::parseParams();

        if (isset($_GET["catID"])) {
            $this->catID = (int)$_GET["catID"];
        }
        if (isset($_GET["prodID"])) {
            $this->prodID = (int)$_GET["prodID"];
        }
    }

    public function _render(JSONResponse $req)
    {
        $field = new ArrayDataInput("value", "Category Attributes", 0);
        $field->source_label_visible = TRUE;

        $bean1 = new ClassAttributeValuesBean();
        $field->getProcessor()->setTransactBean($bean1);

        $rend = new ClassAttributeField($field);

        $rend->setCategoryID($this->catID);
        $rend->setProductID($this->prodID);

        $rend->renderImpl();

    }
}

class ClassAttributeField extends DataIteratorField
{

    protected $catID = -1;
    protected $prodID = -1;

    public function __construct(DataInput $input)
    {
        parent::__construct($input);
        $this->setItemRenderer(new ClassAttributeItem());

        $cab = new ClassAttributesBean();
        $this->setIterator($cab->queryFull());
        $this->getItemRenderer()->setValueKey("caID");
        $this->getItemRenderer()->setLabelKey("attribute_name");

    }

    public function requiredStyle(): array
    {
        $arr = parent::requiredStyle();
        $arr[] = STORE_LOCAL . "/css/ClassAttributeField.css";
        return $arr;
    }

    public function setCategoryID($catID)
    {
        $this->catID = $catID;
        $this->iterator->select->fields()->set("ca.*", "ma.name AS attribute_name", "ma.unit", "ma.type");
        $this->iterator->select->from = $this->iterator->name() . " ca, attributes ma ";
        $this->iterator->select->where()->add("ca.catID", $catID);
        $this->iterator->select->where()->add("ma.maID", "ca.maID");

    }

    public function setProductID($prodID)
    {
        $this->prodID = (int)$prodID;
        if ($this->prodID > 0) {
            $this->iterator->select->fields()->set("ca.*", "ma.name AS attribute_name", "ma.unit", "ma.type", "cav.value", "cav.prodID");
            $this->iterator->select->from = $this->iterator->name() . " ca LEFT JOIN class_attribute_values cav ON ca.caID = cav.caID , attributes ma ";
            $this->iterator->select->where()->add("ma.maID", "ca.maID");
            $this->iterator->select->where()->add("ca.catID", $this->catID);
            $this->iterator->select->group_by = "ca.caID";
            $this->iterator->select->having = "(cav.prodID='{$this->prodID}' OR cav.prodID IS NULL)";
        }
    }

    public function renderImpl()
    {

        if ($this->catID < 1) {

            echo tr("Select product category first");
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
                console.log("Adding category handler");

                $("[name='catID']").on("change", function () {
                    console.log("Category Changed");

                    var catID = $(this).val();

                    var req = new JSONRequest();
                    req.setResponder("ClassAttributeField");
                    req.setFunction("render");
                    req.setParameter("catID", catID);
                    req.setParameter("prodID", <?php echo $this->prodID;?>);

                    req.start(
                        function (request_result) {
                            var result = request_result.json_result;
                            var html = result.contents;
                            $(".ClassAttributeField[field='<?php echo $this->input->getName();?>']").html(html);
                        },
                        function (request_error) {
                            showAlert(request_error.description);
                        }
                    );

                });
            });
        </script>
        <?php
    }

}

?>
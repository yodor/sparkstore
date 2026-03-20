<?php
include_once("objects/sql/ClosureFilter.php");

class AttributeFilter extends ClosureFilter {

    /**
     * Inner join product_attributes to sellable_products matching
     * product_attributes.attribute_name with DataInput->getName() and
     * product_attributes.attribute_value with DataInput->value() under getMatchValue helper for MATCH_LIKE
     *
     * @param string $title
     * @param int $matchMode
     */
    public function __construct(string $title, int $matchMode = ClosureFilter::MATCH_LIKE)
    {

        $attributeClosure = function(SQLSelect $select, DataInput $input) {

            $opr = $this->getMatchOperator($input);
            $value = $this->getMatchValue($input);

            $filterName = "F_".Spark::Hash($input->getName());
            $select->from()->innerJoin("product_attributes $filterName")->on("$filterName.prodID = sellable_products.prodID");

            $select->where()->add("$filterName.attribute_name", $input->getName());
            $select->where()->add("$filterName.attribute_value", $value, $opr);

            //echo $select->debugSQL();
//            $select->setMeta("AttrubuteFilter Query");
        };

        parent::__construct($title, $attributeClosure);
        $this->matchMode = $matchMode;
    }

}
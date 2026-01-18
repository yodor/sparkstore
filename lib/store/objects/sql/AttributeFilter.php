<?php
include_once("objects/sql/ClosureFilter.php");

class AttributeFilter extends ClosureFilter {

    /**
     * Join product attributes to  sellables
     * @param string $title
     * @param int $matchMode
     */
    public function __construct(string $title, int $matchMode = ClosureFilter::MATCH_LIKE)
    {

        $attributeClosure = function(SQLSelect $select, DataInput $input) {

            $opr = $this->getMatchOperator($input);
            $value = $this->getMatchValue($input);

//            $select->fields()->setExpression("{$input->getName()}.value", "{$input->getName()}");
//            $select->from .= " INNER JOIN
//            (SELECT
//                pcav.prodID, pcav.value
//                FROM product_class_attribute_values pcav
//                INNER JOIN product_class_attributes pca ON pca.pcaID = pcav.pcaID
//                INNER JOIN attributes a ON a.attrID = pca.attrID AND a.name = '{$input->getName()}' AND pcav.value $opr '{$value}'
//            ) AS {$input->getName()}
//                ON {$input->getName()}.prodID = sellable_products.prodID";

            $select->fields()->setExpression("{$input->getName()}.attribute_value", "{$input->getName()}");
            $select->from .= " INNER JOIN product_attributes AS {$input->getName()} ON {$input->getName()}.prodID = sellable_products.prodID ";
            $select->where()->add("{$input->getName()}.attribute_name", "'{$input->getName()}'");
            $select->where()->add("{$input->getName()}.attribute_value", "'{$value}'", $opr);
//            echo $select->getSQL();
        };
        parent::__construct($title, $attributeClosure);
        $this->matchMode = $matchMode;
    }

}
?>
<?php
include_once("objects/sql/ClosureFilter.php");

class PriceFilter extends ClosureFilter {
    public function __construct(string $title)
    {

        $closure_price = function(SQLSelect $select, DataInput $input) {
            $opr = "<=";
            if (strcmp($this->getName(), "price_min") === 0) {
                $opr = ">=";
            }

            $value = (int)$input->getValue();
            $iterator = $select->where()->iterator();

            while ($clause = $iterator->next()) {
                if ($clause instanceof SQLClause) {
                    if (strcmp($clause->getExpression(), "sell_price") === 0) {
                        if ($clause->getValue()>$value) {
                            $clause->setOperator("<=");
                            $opr = ">=";
                            $select->order_by = "sell_price DESC";
                        }
                        else {
                            $select->order_by = "sell_price ASC";
                        }
                        break;
                    }
                }
            }

            $select->where()->add("sell_price", "$value", $opr);
        };
        parent::__construct($title, $closure_price);
    }
}
?>
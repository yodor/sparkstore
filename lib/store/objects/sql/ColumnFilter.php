<?php
include_once("objects/sql/ClosureFilter.php");

class ColumnFilter extends ClosureFilter
{
    public function __construct(string $title, int $matchMode = ClosureFilter::MATCH_LIKE)
    {
        $closure = function(SQLSelect $select, DataInput $input){
            $select->where()->add($input->getName(), "'{$this->getMatchValue($input)}'", $this->getMatchOperator($input));
        };
        parent::__construct($title, $closure);
        $this->matchMode = $matchMode;
    }
}
?>
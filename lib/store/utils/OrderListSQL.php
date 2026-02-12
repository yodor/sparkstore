<?php
include_once("sql/SQLSelect.php");

class OrderListSQL extends SQLSelect
{
    public function __construct()
    {
        parent::__construct();

        //select additional the items and client - allow search
        $select = new SQLSelect();
        $select->fields()->set("*");
        $select->fields()->setExpression(" (SELECT GROUP_CONCAT('-oi-', oi.product) FROM  order_items oi WHERE oi.orderID=o.orderID) ", "items");
        $select->fields()->setExpression(" (SELECT CONCAT_WS('--', u.fullname, u.email, u.phone) FROM users u WHERE u.userID=o.userID) ", "client");
        $select->from = " orders o ";

        $this->fields()->set("derived.*");

        $this->from = " ( ".$select->getSQL()." ) as derived";

    }
}
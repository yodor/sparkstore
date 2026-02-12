<?php
include_once("sql/SQLSelect.php");

class OrdersSQL extends SQLSelect
{
    public function __construct()
    {
        parent::__construct();

        //select additional the items and client - allow search
        $this->fields()->set("*");
        $this->fields()->setExpression(" (SELECT GROUP_CONCAT('-oi-', oi.product) FROM  order_items oi WHERE oi.orderID=o.orderID) ", "items");
        $this->fields()->setExpression(" (SELECT CONCAT_WS('--', u.fullname, u.email, u.phone) FROM users u WHERE u.userID=o.userID) ", "client");
        $this->from = " orders o  ";

    }
}
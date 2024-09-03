<?php
include_once("session.php");
include_once("templates/admin/BeanListPage.php");
include_once("components/renderers/cells/DateCellRenderer.php");
include_once("components/renderers/cells/NumericCellRenderer.php");
include_once("responders/ToggleFieldResponder.php");

include_once ("beans/UsersBean.php");

$cmp = new BeanListPage();

$cmp->getPage()->navigation()->clear();
$cmp->getPage()->getActions()->removeByAction("Add");

$bean = new UsersBean();
$h_toggle = new ToggleFieldResponder($bean);

$search_fields = array("email", "fullname", "phone");
$cmp->getSearch()->getForm()->setFields($search_fields);



$qry = $bean->query("email", "fullname", "userID", "phone", "last_active", "counter", "date_signup", "suspend");

$cmp->setIterator($qry);

$cmp->setBean($bean);

$cmp->setListFields(array("fullname"    => "Full Name", "email" => "Email", "phone" => "Phone",
                          "date_signup" => "Date Signup", "last_active" => "Last Active", "counter" => "Login Counter",
                          "suspend"     => "Suspend"));

$view = $cmp->initView();
$view->getColumn("date_signup")->setCellRenderer(new DateCellRenderer());
$view->getColumn("last_active")->setCellRenderer(new DateCellRenderer());
$view->getColumn("counter")->setCellRenderer(new NumericCellRenderer("%01.0f"));
$view->getColumn("counter")->setAlignClass(TableColumn::ALIGN_CENTER);

$vis_act = new ActionsCellRenderer();
$check_is_suspend = function (Action $act, array $data) {
    return ($data['suspend'] < 1);
};
$check_is_not_suspend = function (Action $act, array $data) {
    return ($data['suspend'] > 0);
};
$vis_act->getActions()->append($h_toggle->createAction("Disable", "field=suspend&status=1", $check_is_suspend));
$vis_act->getActions()->append($h_toggle->createAction("Enable", "field=suspend&status=0", $check_is_not_suspend));
$cmp->getView()->getColumn("suspend")->setCellRenderer($vis_act);

$cmp->getPage()->getActions()->removeByAction("Add");
$cmp->viewItemActions()->removeByAction("Edit");

$cmp->render();

?>

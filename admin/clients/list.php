<?php
include_once("session.php");
include_once("components/templates/admin/BeanListPage.php");
include_once("components/renderers/cells/DateCell.php");
include_once("components/renderers/cells/NumericCell.php");
include_once("responders/ToggleFieldResponder.php");

include_once ("beans/UsersBean.php");

$cmp = new BeanListPage();

$cmp->getPage()->navigation()->clear();
$cmp->getPage()->getActions()->removeByAction("Add");

$bean = new UsersBean();
$h_toggle = new ToggleFieldResponder($bean);

$search_fields = array("email", "fullname", "phone");
$cmp->getSearch()->getForm()->setColumns($search_fields);



$qry = $bean->query("email", "fullname", "userID", "phone", "last_active", "counter", "date_signup", "suspend");

$cmp->setIterator($qry);

$cmp->setBean($bean);

$cmp->setListFields(array("fullname"    => "Full Name", "email" => "Email", "phone" => "Phone",
                          "date_signup" => "Date Signup", "last_active" => "Last Active", "counter" => "Login Counter",
                          "suspend"     => "Suspend"));

$view = $cmp->initView();
$view->getColumn("date_signup")->setCellRenderer(new DateCell());
$view->getColumn("last_active")->setCellRenderer(new DateCell());
$view->getColumn("counter")->setCellRenderer(new NumericCell("%01.0f"));
$view->getColumn("counter")->setAlignClass(TableColumn::ALIGN_CENTER);

$vis_act = new ActionsCell();
$check_is_suspend = function (Action $act, array $data) {
    return ($data['suspend'] < 1);
};
$disable_action = $h_toggle->createAction("Disable");
$disable_action->getURL()->add(new URLParameter("field", "suspend"));
$disable_action->getURL()->add(new URLParameter("status", "1"));
$disable_action->setCheckCode($check_is_suspend);
$vis_act->getActions()->append($disable_action);

$check_is_not_suspend = function (Action $act, array $data) {
    return ($data['suspend'] > 0);
};
$enable_action = $h_toggle->createAction("Enable");
$enable_action->getURL()->add(new URLParameter("field", "suspend"));
$enable_action->getURL()->add(new URLParameter("status", "0"));
$enable_action->setCheckCode($check_is_not_suspend);
$vis_act->getActions()->append($enable_action);

$cmp->getView()->getColumn("suspend")->setCellRenderer($vis_act);

$cmp->getPage()->getActions()->removeByAction("Add");
$cmp->viewItemActions()->removeByAction("Edit");

$cmp->render();
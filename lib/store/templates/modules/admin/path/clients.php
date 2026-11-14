<?php
include_once("beans/UsersBean.php");
include_once("responders/ToggleFieldResponder.php");
include_once("components/renderers/cells/DateCell.php");
include_once("components/renderers/cells/NumericCell.php");
include_once("components/renderers/cells/ActionsCell.php");

//registered clients - list only, accounts are created by the clients themselves
$bean = new UsersBean();

$config = TemplateConfig::List(UsersBean::class);
$config->title = tr("Clients");
$config->iterator = $bean->query("email", "fullname", "userID", "phone", "last_active", "counter", "date_signup", "suspend");
$config->listFields = array("fullname" => "Full Name", "email" => "Email", "phone" => "Phone",
    "date_signup" => "Date Signup", "last_active" => "Last Active", "counter" => "Login Counter", "suspend" => "Suspend");
$config->searchField = array("email", "fullname", "phone");
$config->clearNavigation = true;

$config->observer = function (TemplateEvent $event) {
    $content = $event->getSource();
    if (!($content instanceof BeanList)) throw new Exception("Incorrect event source - expecting BeanList");

    if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
        $content->setAddEnabled(false);
        $content->setEditEnabled(false);
    }
    else if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {
        $view = $content->tableView();
        $view->getColumn("date_signup")->setCellRenderer(new DateCell());
        $view->getColumn("last_active")->setCellRenderer(new DateCell());
        $view->getColumn("counter")->setCellRenderer(new NumericCell("%01.0f"));
        $view->getColumn("counter")->setAlignClass(TableColumn::ALIGN_CENTER);

        //suspend / enable the account
        $toggle = new ToggleFieldResponder($content->getBean());
        $cell = new ActionsCell();

        $disable = $toggle->createAction("Disable");
        $disable->setURL(Module::PathURL("", $disable->getURL()));
        $disable->getURL()->add(new URLParameter("field", "suspend"));
        $disable->getURL()->add(new URLParameter("status", "1"));
        $disable->setCheckCode(fn(Action $action, array $data) => ($data["suspend"] < 1));
        $cell->getActions()->append($disable);

        $enable = $toggle->createAction("Enable");
        $enable->setURL(Module::PathURL("", $enable->getURL()));
        $enable->getURL()->add(new URLParameter("field", "suspend"));
        $enable->getURL()->add(new URLParameter("status", "0"));
        $enable->setCheckCode(fn(Action $action, array $data) => ($data["suspend"] > 0));
        $cell->getActions()->append($enable);

        $view->getColumn("suspend")->setCellRenderer($cell);
    }
};

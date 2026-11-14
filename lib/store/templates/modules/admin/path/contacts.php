<?php
include_once("store/beans/ContactRequestsBean.php");

//contact requests sent from the site - list and delete
$config = TemplateConfig::List(ContactRequestsBean::class);
$config->title = tr("Contact Requests");
$config->listFields = array("fullname" => "Name", "phone" => "Phone", "email" => "Email", "query" => "Query", "date_created" => "Date");
$config->searchField = array("fullname", "email", "phone");
$config->clearNavigation = true;

$config->observer = function (TemplateEvent $event) {
    $content = $event->getSource();
    if (!($content instanceof BeanList)) throw new Exception("Incorrect event source - expecting BeanList");

    if ($event->isEvent(TemplateEvent::CONTENT_SETUP)) {
        $content->setAddEnabled(false);
        $content->setEditEnabled(false);
    }
    else if ($event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) {
        //entered by visitors - shown as text
        foreach (array("fullname", "phone", "email", "query") as $column) {
            $cell = $content->tableView()->getColumn($column)->getCellRenderer();
            if ($cell instanceof TableCell) $cell->setEscapeContents(true);
        }
    }
};

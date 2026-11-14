<?php
include_once("store/beans/ContactRequestsBean.php");

//contact requests sent from the site - list and delete
$config = TemplateConfig::List(ContactRequestsBean::class);
$config->title = tr("Contact Requests");
$config->listFields = array("fullname" => "Name", "phone" => "Phone", "email" => "Email", "query" => "Query", "date_created" => "Date");
$config->searchField = array("fullname", "email", "phone");
$config->clearNavigation = true;

$config->observer = function (TemplateEvent $event) {
    if (!$event->isEvent(TemplateEvent::CONTENT_SETUP)) return;
    $content = $event->getSource();
    if (!($content instanceof BeanList)) throw new Exception("Incorrect event source - expecting BeanList");
    $content->setAddEnabled(false);
    $content->setEditEnabled(false);
};

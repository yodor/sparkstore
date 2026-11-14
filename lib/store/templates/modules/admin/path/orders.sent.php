<?php
include_once("store/templates/content/OrdersList.php");

$config = TemplateConfig::Factory();
$config->contentClass = OrdersList::class;
$config->title = tr("Sent Orders");
$config->clearNavigation = true;
$config->observer = function (TemplateEvent $event) {
    if (!$event->isEvent(TemplateEvent::CONTENT_CREATED)) return;
    $content = $event->getSource();
    if ($content instanceof OrdersList) $content->setStatus(OrdersBean::STATUS_SENT);
};

<?php
//called before render
$callback = function(SparkEvent $event) {
    if ($event->isEvent(TemplateFactoryEvent::TEMPLATE_CREATED)) {
        $template = $event->getSource();
        if ($template instanceof PageTemplate) {
            $page = $template->getPage();
            if ($page instanceof SparkAdminPage) {
                $page->navigation()->clear();
            }

        }
    }
};
SparkEventManager::register(TemplateFactoryEvent::class, new SparkObserver($callback));
TemplateFactory::RenderPage("ProductsList");
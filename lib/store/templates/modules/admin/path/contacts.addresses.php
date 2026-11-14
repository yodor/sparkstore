<?php
include_once("store/beans/ContactAddressesBean.php");
include_once("store/forms/ContactAddressInputForm.php");

if (URL::Current()->contains("editID")) {
    $config = TemplateConfig::Editor(ContactAddressesBean::class, ContactAddressInputForm::class);
    $config->title = tr("Contact Address");
}
else {
    $config = TemplateConfig::List(ContactAddressesBean::class);
    $config->title = tr("Contact Addresses");
    $config->listFields = array("city" => "City", "address" => "Address", "phone" => "Phone", "email" => "Email");

    $config->observer = function (TemplateEvent $event) {
        if (!$event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) return;
        $content = $event->getSource();
        if (!($content instanceof BeanList)) throw new Exception("Incorrect event source - expecting BeanList");
        $content->tableView()->setDefaultOrder(new OrderColumn("caID", OrderDirection::ASC));
    };
}

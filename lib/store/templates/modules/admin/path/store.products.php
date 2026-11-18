<?php
include_once("store/utils/CheckStockState.php");
include_once("db/DBExpression.php");

if (URL::Current()->contains("editID")) {
    $config = BeanEditor::Configure(ProductsBean::class, ProductInputForm::class);

    //sites change the form (formClass) and add their own handling with TemplateConfig::WrapObserver()
    $config->observer = function (TemplateEvent $event) {
        if (!$event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) return;
        $content = $event->getSource();
        if (!($content instanceof BeanEditor)) return;
        $editor = $content->editor();

        //new products: the insert date (the column has no default)
        $editor->getTransactor()->assignInsertValue("insert_date", new DBExpression("now()"));

        //stock amount changes: in-stock subscribers, back in stock list
        CheckStockState::Observe($editor);
    };
}
else {
    $config = BeanConfig::Factory();

    $config->contentClass = ProductsList::class;

    $config->clearNavigation = true;
}
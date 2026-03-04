<?php

if (URL::Current()->contains("editID")) {
    $config = Template::Editor(ProductCategoriesBean::class, ProductCategoryInputForm::class);
}
else {
    $config = Template::Tree(ProductCategoriesBean::class);

    $config->listFields = array("category_name"=>"Category Name");

    $config->observer = Template::WrapObserver(
    function(TemplateEvent $event)  {
        if (!$event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) return;
        $source = $event->getSource();
        if (!($source instanceof BeanTree)) throw new Exception("Incorrect event source - expecting BeanTree");
        $iterator = $source->getIterator();
        if (!($iterator instanceof SQLQuery)) throw new Exception("TreeView iterator is not SQLQuery");
        $item = $source->treeView()->getItemRenderer();
        if (!($item instanceof TextTreeItem)) throw new Exception("TreeView item renderer is not TextTreeItem");

        $treeView = $source->treeView();
        $treeView->setBranchRenderMode(NestedSetTreeView::MODE_BRANCHES_UNFOLDED);

        $iterator->select->fields()->setExpression("(SELECT pcp.pcpID FROM product_category_photos pcp WHERE pcp.catID = node.catID ORDER BY pcp.position ASC LIMIT 1)", " pcpID ");

        $si = new StorageItem(-1, "ProductCategoryPhotosBean");
        $si->setName("pcpID");
//
        $item->icon()->setStorageItem($si);
        $item->icon()->setPhotoSize(0, 32);

        $bannersAction = TemplateContent::CreateAction("banners", "Banners Gallery", "banners");
        $bannersAction->getURL()->add(new DataParameter("catID", $source->getBean()->key()));
        $item->getActions()->append($bannersAction);

    }, $config->observer);

    $config->clearNavigation = true;
}
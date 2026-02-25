<?php
include_once("templates/TemplateConfig.php");
include_once("templates/Template.php");
final class PathHandler extends SparkObject
{

    public static function Config(string $path, ?int $editID=null) : ?TemplateConfig
    {
        $config = null;
        if (strcmp($path, "/store") === 0) {
            $config = new TemplateConfig();
            $config->contentClass = ContainerContent::class;
            $config->title = "Store";
            $config->textContents = tr("Store Management");
        }
        if (strcmp($path, "/store/attributes")===0) {

            if ($editID) {
                $config = Template::Editor(AttributesBean::class, AttributeInputForm::class);
            }
            else {
                $config = Template::List(AttributesBean::class);
                $config->listFields = array("name"=>"Name","unit"=>"Unit", "type"=>"Type");
            }
        }
        else if (strcmp($path, "/store/brands")===0) {
            if ($editID) {
                $config = Template::Editor(BrandsBean::class, BrandInputForm::class);
            }
            else {
                $config = Template::List(BrandsBean::class);
                $config->listFields = array("cover"=>"Cover","brand_name"=>"Brand Name", "summary"=>"Summary", "url"=>"URL", "home_visible"=>"Home Visible");
                $config->searchField = array("brand_name", "summary", "brandID");
                $config->observer = function(TemplateEvent $event) {
                    if (!$event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) return;
                    $source = $event->getSource();
                    if (!($source instanceof BeanList)) throw new Exception("Incorrect event source - expecting BeanList");
                    $source->tableView()->getColumn("home_visible")->setCellRenderer(new BooleanCell("Yes", "No"));
                    $source->tableView()->getColumn("cover")->setCellRenderer(new ImageCell());
                };
            }
        }
        else if (strcmp($path, "/store/categories")===0) {
            if ($editID) {
                $config = Template::Editor(ProductCategoriesBean::class, ProductCategoryInputForm::class);
            }
            else {
                $config = Template::Tree(ProductCategoriesBean::class);

                $config->listFields = array("category_name"=>"Category Name");

                $config->observer = function(TemplateEvent $event) use($path) {
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

                };
            }

        }
        else if (strcmp($path, "/store/categories/banners")===0) {
            if ($editID) {
                $config = Template::Editor(ProductCategoryBannersBean::class, PhotoForm::class);
                include_once("store/beans/ProductCategoriesBean.php");
                $config->condition = new BeanKeyCondition(new ProductCategoriesBean(), "../list.php", array("category_name"));
                $config->observer = function(TemplateEvent $event) use($path) {
                    if (!$event->isEvent(TemplateEvent::CONTENT_INITIALIZED)) return;
                    $field = DataInputFactory::Create(InputType::TEXT, "link", "Link", 0);
                    $event->getSource()->editor()->getForm()->addInput($field);

                    //$event->getSource()->getConfig()->setName(tr("Banners Gallery") . ": " . $event->getSource()->getConfig()->condition->getData("category_name"));

                };
            }
            else {
                $config = Template::Gallery(ProductCategoryBannersBean::class);
            }

        }

        return $config;
    }



}
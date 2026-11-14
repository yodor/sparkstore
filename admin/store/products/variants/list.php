<?php
include_once("components/templates/admin/BeanListPage.php");

include_once("store/beans/VariantOptionsBean.php");
include_once("store/beans/ProductClassesBean.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ProductVariantsBean.php");

include_once("store/responders/json/VariantPriceFormResponder.php");
include_once("input/renderers/CheckField.php");
include_once("input/validators/EmptyValueValidator.php");
include_once("input/processors/InputProcessor.php");


include_once("store/forms/ProductVariantsInputForm.php");
include_once("store/forms/processors/ProductVariantsProcessor.php");

$menu = array(
//    new MenuItem("Inventory", "inventory/list.php", "list"),
);

$cmp = new BeanListPage();
$req = new BeanKeyCondition(new ProductsBean(),  "../list.php", array("product_name"));

$handler = new VariantPriceFormResponder((int)$req->getValue());

$cmp->getPage()->setPageMenu($menu);

$cmp->getPage()->setName(tr("Product Variants").": ".$req->getData("product_name"));

$form = new ProductVariantsInputForm($req->getValue());

$rend = new FormRenderer($form);
$proc = new ProductVariantsProcessor($req->getValue());
$proc->loadFormData($form);

$proc->process($form);
if ($proc->getMessage()) {
    Session::SetAlert($proc->getMessage());
}

$closure = function(ClosureComponent $cmp) use($rend) {

    echo "<div class='Caption'>".tr("Варианти за този продукт - избираеми преди покупка")."</div>";
    echo "<br>";

    $rend->render();

    echo "<HR>";
};
$cmp->items()->append(new ClosureComponent($closure));


$bean = new ProductVariantsBean();
$query = $bean->queryProduct((int)$req->getID());

$cmp->setIterator($query);

$cmp->setListFields(array("pvID"=>"pvID", "option_name"=>"Option Name", "option_value"=>"Option Value", "price"));


$view = $cmp->initView();
$col = $cmp->viewItemActions();
$col->clear();

$col->append( new Action("Photo Gallery", "gallery/list.php", array(new DataParameter("pvID"))));

$cmp->getPage()->getActions()->removeByAction(SparkAdminPage::ACTION_ADD);
$cmp->render();
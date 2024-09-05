<?php
include_once("session.php");
include_once("templates/admin/BeanListPage.php");

include_once("store/beans/VariantOptionsBean.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ProductClassesBean.php");
include_once("utils/GETProcessor.php");
include_once("components/ClosureComponent.php");

$menu = array(
//    new MenuItem("Inventory", "inventory/list.php", "list"),
);

$cmp = new BeanListPage();

$cmp->getPage()->navigation()->clear();


$title = tr("Options List");

$classes = new ProductClassesBean();
$products = new ProductsBean();

$bean = new VariantOptionsBean();
$bean->select()->where()->add("parentID" , " NULL ", " IS ");
$pclsID = -1;

$product_filter = new GETProcessor("Продукт", "prodID");
$product_filter->setSQLSelect($bean->select());

$class_filter = new GETProcessor("Продуктов клас", "pclsID");
$class_filter->setSQLSelect($bean->select());


$product_filter->processInput();
if ($product_filter->isProcessed()) {
    $prodID = (int)$product_filter->getValue();
    if ($prodID>0) {
        try {
            $product_data = $products->getByID($prodID, "product_name");
            //$bean->select()->where()->add("pclsID", $pclsID);
            $title = tr("Options List") . " - " . tr("Product") . ": " . $product_data["product_name"];
        }
        catch (Exception $e) {
            Session::SetAlert("Requested product is not accessible");
        }
    }
    else {
        $bean->select()->where()->add("pclsID" , " NULL ", " IS ");
        $bean->select()->where()->add("prodID" , " NULL ", " IS ");
    }
}
else {
    $class_filter->processInput();
    if ($class_filter->isProcessed()) {
        $pclsID = (int)$class_filter->getValue();
        if ($pclsID>0) {
            try {
                $class_data = $classes->getByID($pclsID);
                //$bean->select()->where()->add("pclsID", $pclsID);
                $title = tr("Options List") . " - " . tr("Class") . ": " . $class_data["class_name"];
            }
            catch (Exception $e) {
                Session::SetAlert("Requested product class is not accessible");
            }
        }
    }
    else {
        $bean->select()->where()->add("pclsID" , " NULL ", " IS ");
        $bean->select()->where()->add("prodID" , " NULL ", " IS ");
    }
}


$cmp->getPage()->setName($title);

$closure = function(ClosureComponent $cmp) use ($class_filter, $classes) {

    $input = DataInputFactory::Create(DataInputFactory::SELECT, $class_filter->getName(), $class_filter->getTitle(), 0);
    $input->getRenderer()->setIterator($classes->query("pclsID", "class_name"));
    $input->getRenderer()->getItemRenderer()->setLabelKey("class_name");
    $input->getRenderer()->getItemRenderer()->setValueKey("pclsID");
    $input->getRenderer()->na_label = "--- Всички ---";
    $input->getRenderer()->setInputAttribute("onChange", "document.forms.Filters.submit()");
    if ($class_filter->isProcessed()) {
        $input->setValue($class_filter->getValue());
    }
    echo "<form name='Filters'>";
    $cmp = new InputComponent($input);
    $cmp->render();
    echo "</form>";
};
if (!$product_filter->isProcessed()) {
    $cmp->items()->append(new ClosureComponent($closure));
}

$cmp->setBean($bean);


$cmp->setListFields(array("voID"=>"ID", "position"=>"Position", "option_name"=>"Option Name", "parameters"=>"Parameters"));
$query = $bean->queryFull();
$query->select->fields()->setExpression("(SELECT GROUP_CONCAT(vopt.option_value ORDER BY vopt.position ASC SEPARATOR ';' ) FROM variant_options vopt WHERE vopt.parentID = variant_options.voID )", "parameters");
$cmp->setIterator($query);


$view = $cmp->initView();

$act = $cmp->viewItemActions();

$act->append(Action::RowSeparator());

$act->append(new Action("Parameters", "parameters/list.php", array(new DataParameter("voID"))));

//за изграждане на варианти на продукта от съответния клас
$closure = function(ClosureComponent $cmp) {
    echo "<div class='help summary'>";
    echo "Тук може да добавяте опции за изграждане на продуктови варианти.<BR>";
    echo "</div>";
};
$cmp->items()->insert(new ClosureComponent($closure), 0);
$cmp->render();


?>

<?php
include_once("session.php");
include_once("templates/admin/BeanListPage.php");
include_once("store/beans/SectionsBean.php");

$cmp = new BeanListPage();

$cmp->setListFields(array("position"=>"#", "section_title"=>"Section"));

$bean = new SectionsBean();
$cmp->setBean($bean);

$cmp->initView();
//$cmp->getView()->setDefaultOrder(" position ASC ");
$cmp->viewItemActions()->append(Action::RowSeparator());
$cmp->viewItemActions()->append(new Action("Banners Gallery", "banners/list.php", array(new DataParameter("secID", $bean->key()))));
$cmp->viewItemActions()->append(Action::RowSeparator());
$cmp->viewItemActions()->append(
    new Action("Products", ADMIN_LOCAL."/store/products/list.php",
        array(
           new DataParameter("filter_section", "section_title"),
           new URLParameter("SubmitForm", "ProductFilterInputForm"),
        )
    )
);


$closure = function(ClosureComponent $cmp) {
    echo "<div class='help summary'>";
    echo "Тук добавяте секции за изграждане на списъци от продукти за извеждане в началната страница.<BR>";
    echo "</div>";
};
$cmp->items()->insert(new ClosureComponent($closure), 0);
$cmp->render();
?>

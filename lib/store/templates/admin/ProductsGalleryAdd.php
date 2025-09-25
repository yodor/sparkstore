<?php
include_once("templates/admin/BeanEditorPage.php");
include_once("forms/PhotoForm.php");
include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ProductsBean.php");

class ProductsGalleryAdd extends BeanEditorPage
{

    public function __construct()
    {
        parent::__construct();
        $this->setBean(new ProductPhotosBean());
        $this->setForm(new PhotoForm());
        $rc = new BeanKeyCondition(new ProductsBean(), "../list.php");
        $this->setRequestCondition($rc);
        $this->page->setName(tr("Image").": ".tr("Product Gallery") . ": " . $rc->getData("product_name"));

        $this->getBean()->select()->where()->addURLParameter($rc->getURLParameter());
    }

    public function initView()
    {
        parent::initView();
        $rc = $this->getRequestCondition();
        if ($rc instanceof BeanKeyCondition) {
            $this->getEditor()->getTransactor()->appendURLParameter($rc->getURLParameter());
        }
    }

}

$template = new ProductsGalleryAdd();
?>

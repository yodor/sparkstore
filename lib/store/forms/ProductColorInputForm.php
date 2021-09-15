<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");
include_once("store/beans/ProductColorPhotosBean.php");
include_once("store/beans/StoreColorsBean.php");

class ProductColorInputForm extends InputForm
{

    public function __construct(int $prodID)
    {

        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::SELECT, "color", "Color Code", 1);

        $rend = $field->getRenderer();
        $scb = new StoreColorsBean();
        $iterator = $scb->query($scb->key(), "color","color_code");
        $iterator->select->order_by = " color ASC ";
        $rend->setIterator($iterator);
        $rend->getItemRenderer()->setValueKey("color");
        $rend->getItemRenderer()->setLabelKey("color");

        $action = new Action("new", "../../colors/add.php");
        $action->setContents(tr("Нов цветови код"));
        $rend->getAddonContainer()->append($action);

        $opt = $rend->getItemRenderer();
        $opt->addDataAttribute("color_code");

        $this->addInput($field);
        // 	$field->enableTranslator(true);

        $input = DataInputFactory::Create(DataInputFactory::SESSION_IMAGE, "color_photo", "Color Chip", 0);
        $this->addInput($input);

        $input = DataInputFactory::Create(DataInputFactory::SESSION_IMAGE, "photo", "Product Photos", 0);
        $bean = new ProductColorPhotosBean();

        $input->getProcessor()->setTransactBean($bean,10);
        $this->addInput($input);

    }

    public function loadBeanData($editID, DBTableBean $bean)
    {

        $item_row = parent::loadBeanData($editID, $bean);
        //       $pclrID = (int)$item_row["pclrID"];
        //       $this->getInput("photo")->getSource()->setFilter(" pclrID ='$pclrID' ");

    }

    public function loadPostData(array $arr)
    {
        parent::loadPostData($arr);
        //       $pclrID = -1;
        //       $this->getInput("photo")->getSource()->setFilter(" pclrID ='$pclrID' ");

    }
}

?>

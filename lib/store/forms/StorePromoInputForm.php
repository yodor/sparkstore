<?php
include_once("forms/InputForm.php");
include_once("store/beans/ProductCategoriesBean.php");

include_once("input/validators/NumericValidator.php");
include_once("input/validators/DateValidator.php");

class StorePromoInputForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $input = DataInputFactory::Create(InputType::DATE, "start_date", "Start Date", 1);
        $this->addInput($input);

        $input = DataInputFactory::Create(InputType::DATE, "end_date", "End Date", 1);
        $this->addInput($input);

        $field = DataInputFactory::Create(InputType::NESTED_SELECT, "targetID", "Category", 1);
        $bean1 = new ProductCategoriesBean();
        $rend = $field->getRenderer();
        $rend->setIterator(new SQLQuery($bean1->selectTree(array("category_name")), "catID"));
        $rend->getItemRenderer()->setValueKey("catID");
        $rend->getItemRenderer()->setLabelKey("category_name");
        $this->addInput($field);

        $input = DataInputFactory::Create(InputType::TEXT, "discount_percent", "Discount Percent", 1);
        $input->setValidator(new NumericValidator());
        $this->addInput($input);

    }

    public function validate(): void
    {

        parent::validate();
        $start = $this->getInput("start_date");
        $end = $this->getInput("end_date");
        if (!$start->haveError() && !$end->haveError()) {
            $time_start = DateValidator::getTimestamp($start->getValue());
            $time_end = DateValidator::getTimestamp($end->getValue());
            if (((int)$time_start == (int)$time_end) || ((int)$time_start > (int)$time_end)) {
                $this->getInput("start_date")->setError("Start Date is same or ahead of End Date");
                $this->getInput("end_date")->setError("Start Date is same or ahead of End Date");
            }
        }

    }

}

?>
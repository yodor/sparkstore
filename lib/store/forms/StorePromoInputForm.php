<?php
include_once("forms/InputForm.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/SectionsBean.php");
include_once("store/input/processors/PromoTargetProcessor.php");
include_once("store/utils/CatalogParts.php");

include_once("input/validators/NumericValidator.php");
include_once("input/validators/DateValidator.php");

/**
 * A promotion: a discount in percent on a category (with its sub categories) or a section, from the start date to the
 * end date (both days included). It wins over the products' own promo price (ProductsSQL::sellPrice).
 */
class StorePromoInputForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $input = DataInputFactory::Create(InputType::DATE, "start_date", "Start Date", 1);
        $this->addInput($input);

        $input = DataInputFactory::Create(InputType::DATE, "end_date", "End Date", 1);
        $this->addInput($input);

        //the target: a category or a section - one of the two
        $field = DataInputFactory::Create(InputType::NESTED_SELECT, "target_category", "Category", 0);
        new PromoTargetProcessor($field, "Category");
        $bean1 = new ProductCategoriesBean();
        $rend = $field->getRenderer();
        $rend->setIterator(new SelectQuery($bean1->selectTree(array("category_name")), "catID"));
        $rend->getItemRenderer()->setValueKey("catID");
        $rend->getItemRenderer()->setLabelKey("category_name");
        $this->addInput($field);

        if (CatalogParts::Enabled(CatalogParts::SECTIONS)) {
            $field = DataInputFactory::Create(InputType::SELECT, "target_section", "Section", 0);
            new PromoTargetProcessor($field, "Section");
            $sections = new SectionsBean();
            $rend = $field->getRenderer();
            $rend->setIterator($sections->query($sections->key(), "section_title"));
            $rend->getItemRenderer()->setValueKey($sections->key());
            $rend->getItemRenderer()->setLabelKey("section_title");
            $this->addInput($field);
        }

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
            //one day promotions: the end date may be the start date (both days are included)
            $time_start = DateValidator::getTimestamp($start->getValue());
            $time_end = DateValidator::getTimestamp($end->getValue());
            if ((int)$time_start > (int)$time_end) {
                $start->setError(tr("The start date is after the end date"));
                $end->setError(tr("The start date is after the end date"));
            }
        }

        $category = (int)$this->getInput("target_category")->getValue();
        $section = $this->haveInput("target_section") ? (int)$this->getInput("target_section")->getValue() : 0;
        if (($category > 0) === ($section > 0)) {
            $message = tr("Choose a category or a section");
            $this->getInput("target_category")->setError($message);
            if ($this->haveInput("target_section")) $this->getInput("target_section")->setError($message);
        }

        $discount = $this->getInput("discount_percent");
        if (!$discount->haveError()) {
            $value = (float)$discount->getValue();
            if ($value <= 0 || $value >= 100) $discount->setError(tr("A discount between 1 and 99"));
        }
    }

}

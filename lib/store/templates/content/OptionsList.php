<?php
include_once("templates/content/BeanList.php");
include_once("store/beans/VariantOptionsBean.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ProductClassesBean.php");
include_once("utils/GETProcessor.php");
include_once("components/ClosureComponent.php");

class OptionsList extends BeanList
{
    protected GETProcessor $product_filter;
    protected GETProcessor $class_filter;

    protected Form $filters;

    public function __construct(){
        parent::__construct();


        $bean = new VariantOptionsBean();
        $bean->select()->where()->add("parentID" , " NULL ", " IS ");
        $this->setBean($bean);

        $this->product_filter = new GETProcessor("Продукт", "prodID");
        $this->product_filter->setSQLSelect($bean->select());

        $this->class_filter = new GETProcessor("Продуктов клас", "pclsID");
        $this->class_filter->setSQLSelect($bean->select());

        $this->setListFields(array("voID"=>"ID", "position"=>"Position", "option_name"=>"Option Name", "parameters"=>"Parameters"));

        $query = $bean->queryFull();
        $query->select->fields()->setExpression("(SELECT GROUP_CONCAT(vopt.option_value ORDER BY vopt.position ASC SEPARATOR ';' ) FROM variant_options vopt WHERE vopt.parentID = variant_options.voID )", "parameters");
        $this->setIterator($query);

        //create filters form
        $input = DataInputFactory::Create(InputType::SELECT, $this->class_filter->getName(), $this->class_filter->getTitle(), 0);
        $renderer = $input->getRenderer();
        $classes = new ProductClassesBean();
        $renderer->setIterator($classes->query("pclsID", "class_name"));
        $renderer->getItemRenderer()->setLabelKey("class_name");
        $renderer->getItemRenderer()->setValueKey("pclsID");

        if ($renderer instanceof SelectField) {
            $renderer->setDefaultOption("--- Всички ---");
        }

        $renderer->input()?->setAttribute("onChange", "document.forms.Filters.submit()");
        if ($this->class_filter->isProcessed()) {
            $input->setValue($this->class_filter->getValue());
        }

        $form = new Form();
        $form->setName("Filters");
        $form->items()->append(new InputComponent($input));
        $this->filters = $form;

    }
    public function processInput(): void
    {
        parent::processInput();

        $this->product_filter->processInput();
        if ($this->product_filter->isProcessed()) {

            $this->filters->setRenderEnabled(false);

            $prodID = (int)$this->product_filter->getValue();
            if ($prodID>0) {
                try {
                    $products = new ProductsBean();
                    $product_data = $products->getByID($prodID, "product_name");

                    $this->config->title = tr("Options List") . " - " . tr("Product") . ": " . $product_data["product_name"];
                }
                catch (Exception $e) {
                    Session::SetAlert("Requested product is not accessible");
                }
            }
            else {
                $this->bean->select()->where()->add("pclsID" , " NULL ", " IS ");
                $this->bean->select()->where()->add("prodID" , " NULL ", " IS ");
            }


        }
        else {

            //$cmp->getPage()->navigation()->clear();

            $this->class_filter->processInput();

            if ($this->class_filter->isProcessed()) {
                $pclsID = (int)$this->class_filter->getValue();
                if ($pclsID>0) {
                    try {
                        $classes = new ProductClassesBean();
                        $class_data = $classes->getByID($pclsID);

                        $this->config->title = tr("Options List") . " - " . tr("Class") . ": " . $class_data["class_name"];
                    }
                    catch (Exception $e) {
                        Session::SetAlert("Requested product class is not accessible");
                    }
                }
            }
            else {
                $this->bean->select()->where()->add("pclsID" , " NULL ", " IS ");
                $this->bean->select()->where()->add("prodID" , " NULL ", " IS ");
            }
        }


    }

    public function fillPageFilters(Container $filters): void
    {
        parent::fillPageFilters($filters);
        $filters->items()->append($this->filters);
    }

    protected function initItemActions(ActionCollection $act): void
    {
        parent::initItemActions($act);
        $act->append(Action::RowSeparator());
        $paramAction = TemplateContent::CreateAction("Parameters", "Parameters", "parameters");
        $paramAction->getURL()->add(new DataParameter("voID"));
        $act->append($paramAction);

    }
}
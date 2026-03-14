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

    public function __construct()
    {

        parent::__construct();


        $bean = new VariantOptionsBean();
        $this->setBean($bean);

        $bean->select()->fields()->set(...$bean->columnNames());
        $bean->select()->fields()->setAliasExpression("(SELECT GROUP_CONCAT(vopt.option_value ORDER BY vopt.position ASC SEPARATOR ';' ) FROM variant_options vopt WHERE vopt.parentID = variant_options.voID )", "parameters");
        //$bean->select()->fields()->setExpression("(SELECT GROUP_CONCAT(pcls.class_name) FROM product_classes pcls WHERE pcls.pclsID = variant_options.pclsID)", "class_name");

        //only options not their parameters
        $bean->select()->where()->add("parentID" , " NULL ", " IS ");

        //set query to reference bean->select() so changes to select are reflected to the other users - used in responders set position (getMaxPosition()) etc
        $this->setIterator(new SQLQuery($bean->select(), $bean->key(), $bean->getTableName()));

        $this->setListFields(array("voID"=>"ID", "position"=>"Position", "option_name"=>"Option Name", "parameters"=>"Parameters"));

        $this->product_filter = new GETProcessor("Продукт", "prodID");
        //manipulate the bean select
        $this->product_filter->setSQLSelect($bean->select());


        $this->class_filter = new GETProcessor("Опции към клас", "pclsID");
        //manipulate the bean select
        $this->class_filter->setSQLSelect($bean->select());


        //create filters form

        $form = new Form();
        $form->setName("Filters");
        $form->setMethod(Form::METHOD_GET);
        $this->filters = $form;

        $input = DataInputFactory::Create(InputType::SELECT, $this->class_filter->getName(), $this->class_filter->getTitle(), 0);
        $renderer = $input->getRenderer();
        $classes = new ProductClassesBean();
        $renderer->setIterator($classes->query("pclsID", "class_name"));
        $renderer->getItemRenderer()->setLabelKey("class_name");
        $renderer->getItemRenderer()->setValueKey("pclsID");

        if ($renderer instanceof SelectField) {
            $renderer->setDefaultOption("--- Empty ---");
        }

        $renderer->input()?->setAttribute("onChange", "document.forms.Filters.submit()");

        $form->items()->append(new InputComponent($input));


    }
    public function processInput(): void
    {
        parent::processInput();

        $this->product_filter->processInput();

        //show only product options
        if ($this->product_filter->isProcessed()) {

            $this->config->clearNavigation = false;

            //disable class selection form
            $this->filters->setRenderEnabled(false);

            try {
                $prodID = (int)$this->product_filter->getValue();
                $products = new ProductsBean();
                $product_data = $products->getByID($prodID, "product_name");
                $this->config->title = tr("Options List") . " - " . tr("Product") . ": " . $product_data["product_name"];
            }
            catch (Exception $e) {
                Session::SetAlert("Requested product is not accessible");
            }

            $this->query->stmt->where()->add("pclsID", "NULL" , " IS ");
        }
        else {

            $this->class_filter->processInput();

            //if class was selected
            if ($this->class_filter->isProcessed()) {

                $this->config->clearNavigation = false;

                $pclsID = (int)$this->class_filter->getValue();

                $icmp = $this->filters->items()->getByComponentClass("InputComponent");

                if ($icmp instanceof InputComponent) {
                    $icmp->getDataInput()->setValue($pclsID);
                }

                try {
                    $classes = new ProductClassesBean();
                    $class_data = $classes->getByID($pclsID);

                    $this->config->title = tr("Options List") . " - " . tr("Class") . ": " . $class_data["class_name"];
                }
                catch (Exception $e) {
                    Session::SetAlert("Requested product class is not accessible");
                }
                $this->query->stmt->where()->add("prodID", "NULL" , " IS ");

            }
            //neither prodID nor pclsID - show only top level options
            else {
                $this->config->clearNavigation = true;
                $this->query->stmt->where()->add("pclsID", "NULL" , " IS ");
                $this->query->stmt->where()->add("prodID", "NULL" , " IS ");
            }

        }

        //echo $this->query->select->getSQL();
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
        $paramAction->setTooltip(tr("Modify option parameters"));
        $paramAction->getURL()->add(new DataParameter("voID"));
        $act->append($paramAction);

    }

    public function setup(TemplateConfig $config): void
    {
        parent::setup($config);
        $config->title = tr("Options List");

        $config->clearNavigation = true;
    }
}
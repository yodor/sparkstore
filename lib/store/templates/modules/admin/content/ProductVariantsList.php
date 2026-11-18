<?php
include_once("templates/content/BeanList.php");
include_once("store/beans/ProductVariantsBean.php");
include_once("store/forms/ProductVariantsInputForm.php");
include_once("store/forms/processors/ProductVariantsProcessor.php");
include_once("store/responders/json/VariantPriceFormResponder.php");
include_once("forms/renderers/FormRenderer.php");
include_once("components/TextComponent.php");

/**
 * Variants of the product from Template::Condition() (ProductsBean):
 * the variant options form on top, the list of the resulting variants below.
 */
class ProductVariantsList extends BeanList
{
    protected int $prodID = -1;

    protected ProductVariantsInputForm $form;
    protected ProductVariantsProcessor $processor;

    /**
     * Form on top, list below - the page shows this container, tableView() still returns the list
     * @var Container
     */
    protected Container $container;

    protected ?TableView $table = null;

    public function setup(TemplateConfig $config): void
    {
        $condition = Template::Condition();
        if (is_null($condition)) throw new Exception("Product condition not set");
        $this->prodID = (int)$condition->getID();

        $bean = new ProductVariantsBean();
        $config->iterator = $bean->queryProduct($this->prodID);
        $config->listFields = array("option_name" => "Option Name", "option_value" => "Option Value", "variant_price" => "Price");

        parent::setup($config);

        $this->form = new ProductVariantsInputForm($this->prodID);
        $this->processor = new ProductVariantsProcessor($this->prodID);

        //variant price dialog
        new VariantPriceFormResponder($this->prodID);
    }

    public function processInput(): void
    {
        parent::processInput();

        $this->processor->loadFormData($this->form);
        $this->processor->process($this->form);
        if ($this->processor->getMessage()) {
            Session::SetAlert($this->processor->getMessage());
        }
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->table = parent::tableView();

        $this->container = new Container(false);
        $this->container->setComponentClass("ProductVariants");

        $caption = new TextComponent(tr("Варианти за този продукт - избираеми преди покупка"));
        $caption->setComponentClass("Caption");
        $this->container->items()->append($caption);
        $this->container->items()->append(new FormRenderer($this->form));
        $this->container->items()->append($this->table);

        $this->cmp = $this->container;
    }

    public function tableView(): TableView
    {
        if ($this->table instanceof TableView) return $this->table;
        return parent::tableView();
    }

    /**
     * Variants are created from the options form - no "Add"
     */
    public function fillPageActions(ActionCollection $collection): void
    {
    }

    protected function initItemActions(ActionCollection $act): void
    {
        $gallery = TemplateContent::CreateAction("Photo Gallery", tr("Photo Gallery"), "gallery/");
        $gallery->getURL()->add(new DataParameter("pvID"));
        $act->append($gallery);
    }

    protected function getContentTitle(): string
    {
        return tr("Product Variants");
    }
}

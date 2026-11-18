 <?php
include_once("templates/TemplateContent.php");
include_once("components/Container.php");
include_once("components/TextComponent.php");
include_once("components/ItemView.php");
include_once("components/Action.php");
include_once("store/components/AlphabetChooser.php");
include_once("store/utils/url/ProductListURL.php");

/**
 * The values of a product attribute as an index - authors, publishers, brands: a caption, letter links, the values
 * (a link each to the products list with that value) in pages. The keyword search of the page header searches the
 * values here.
 *
 * Path file settings (TemplateConfig data):
 *  - attribute   the attribute name ("Автор") - required
 *  - alphabets   the letter rows (AlphabetChooser), optional
 *  - letter      the word before the selected letter in the title, default tr("Letter")
 *  - placeholder the text of the header search, default tr("Search") + attribute
 * The title is the config title ("Автори"); a letter adds " | <letter>: X".
 *
 * Markup: the body gets the class 'AttributeIndex'; h2.Caption, div.AlphabetChooser, div.ItemView > a.Item
 */
class AttributeIndex extends TemplateContent
{
    protected string $attribute = "";

    protected ?AlphabetChooser $chooser = null;

    protected ?ItemView $list = null;

    protected ?SelectQuery $query = null;

    public function initialize(): void
    {
        $this->attribute = (string)($this->config->data["attribute"] ?? "");
        if ($this->attribute === "") throw new Exception("AttributeIndex: no attribute set");

        $page = SparkPage::Instance();
        $page->body()->addClassName("AttributeIndex");
        $page->head()->addCanonicalParameter("letter");

        $this->chooser = new AlphabetChooser();
        if (isset($this->config->data["alphabets"])) $this->chooser->alphabets = $this->config->data["alphabets"];
        $this->chooser->getURL()->setClearParams("keyword", "filter");

        $select = SQLSelect::Table("product_attributes");
        $select->column("attribute_value")->setAlias("value");
        $select->where()->match("attribute_name", $this->attribute);
        $select->order("value", OrderDirection::ASC);
        $select->group_by = " product_attributes.attribute_value ";

        $this->query = new SelectQuery($select);
        $this->query->setKey("value");

        //each value: the products list with this attribute value
        $item = new Action();
        $item->setClassName("Item");
        $item->setURL(new ProductListURL());
        $item->action_as_contents = false;
        $item->getURL()->add(new DataParameter($this->attribute, "value"));
        $item->addDataAttribute("title", "value");
        $item->setValueKey("value");

        $this->list = new ItemView($this->query);
        $this->list->setName($this->attribute);
        $this->list->setCacheable(true);
        $this->list->setItemsPerGroup(20);
        $this->list->setItemsPerPage(60);
        $this->list->getHeader()->setRenderEnabled(false);
        $this->list->setItemRenderer($item);

        //the header search: the values on this page
        if ($page instanceof StorePageBase) {
            $search = $page->getKeywordSearch();
            $search->setAction(new URL(URL::Requested()->getScriptName()));
            $search->getForm()->setColumns(array("attribute_value"));
            $placeholder = (string)($this->config->data["placeholder"] ?? (tr("Search") . " " . mb_strtolower($this->attribute) . " ..."));
            $search->getForm()->getInput("keyword")->getRenderer()->input()?->setAttribute("placeholder", $placeholder);
        }

        $this->cmp = new Container(false);

        $caption = new TextComponent("", "Caption");
        $caption->setTagName("h2");
        $caption->setText($this->config->title);
        $this->cmp->items()->append($caption);

        $this->cmp->items()->append($this->chooser);
        $this->cmp->items()->append($this->list);
    }

    public function processInput(): void
    {
        $page = SparkPage::Instance();
        $search = ($page instanceof StorePageBase) ? $page->getKeywordSearch() : null;
        if ($search instanceof KeywordSearch) $search->processInput();

        if ($search instanceof KeywordSearch && $search->isProcessed()) {
            $search->getForm()->prepareClauseCollection("AND")->copyTo($this->query->stmt->where());
        }
        else if ($this->chooser->getSelectedLetter()) {
            $this->query->stmt->where()->expression("attribute_value LIKE :selected_letter");
            $this->query->stmt->where()->bind(":selected_letter", $this->chooser->getSelectedLetter() . "%");
            $label = (string)($this->config->data["letter"] ?? tr("Letter"));
            $this->config->title .= " | " . $label . ": " . $this->chooser->getSelectedLetter();
        }

        parent::processInput();
    }

    public function finalizeHead(SparkPage $page): void
    {
        //prev / next of the values pages, the page number in the title
        AbstractResultView::AppendHeadLinks($this->list, $page);
    }

    public function component(): Component
    {
        return $this->cmp;
    }

    protected function getContentTitle(): string
    {
        return $this->attribute;
    }
}
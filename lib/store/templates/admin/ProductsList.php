<?php
include_once("components/templates/admin/BeanListPage.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/ProductClassesBean.php");
include_once("store/beans/ProductSectionsBean.php");
include_once("store/beans/BrandsBean.php");
include_once("store/responders/json/SectionChooserFormResponder.php");
include_once("store/responders/json/ImportUpdateFormResponder.php");
include_once("store/utils/DownloadCSVProducts.php");
include_once("components/InlineScript.php");
include_once("store/beans/ProductViewLogBean.php");

class ScrollTopCookiesScript extends InlineScript implements IPageComponent
{
    protected function finalize() : void
    {
        $code = <<<JS
document.querySelectorAll("[action='Edit']").forEach((element)=>{
    element.addEventListener("click", (event)=>{
        Cookies.set('scrollTop', window.scrollTop, { expires: 0 });
    });
})

let scrollTop = Cookies.get('scrollTop');

if (scrollTop) {
    window.scrollTo(0, scrollTop);
    Cookies.remove('scrollTop');
}
JS;
        $this->setCode($code);
        parent::finalize();
    }
}

class SectionChooserScript extends InlineScript implements IPageComponent
{
    protected function finalize() : void
    {
        $code = <<<JS
function showSectionChooserForm(prodID)
{
    let section_chooser = new JSONFormDialog();
    section_chooser.setTitle("Изберете секции: ");
    section_chooser.setResponder("SectionChooserFormResponder");
    section_chooser.getJSONRequest().setParameter("prodID", prodID);
    section_chooser.show();
}
JS;
        $this->setCode($code);
        parent::finalize();
    }
}

class ImportUpdateScript extends InlineScript implements IPageComponent
{
    protected function finalize() : void
    {
        $code = <<<JS
function showImportUpdateDialog()
{
    let import_dialog = new JSONFormDialog();
    import_dialog.setTitle("Изберете файл: ");
    import_dialog.setResponder("ImportUpdateFormResponder");
    import_dialog.show();
}
JS;
        $this->setCode($code);
        parent::finalize();
    }
}

class ProductsList extends BeanListPage
{

    public function __construct()
    {
        parent::__construct();
        $this->getPage()->setPageMenu(TemplateFactory::MenuForPage("ProductsList"));

        $dcsv_responder = new DownloadCSVProducts();
        $typeNames = $dcsv_responder->getProcessorTypes();

        foreach ($typeNames as $idx=>$typeName) {
            $action = $dcsv_responder->createAction($typeName);
            $this->getPage()->getActions()->append($action);
        }

        $import_responder = new ImportUpdateFormResponder();
        $action = new Action("import_update");
        $action->getURL()->fromString("javascript:showImportUpdateDialog()");
        $action->setTooltip("Import product data from external edit");
        $this->getPage()->getActions()->append($action);


        new ImportUpdateScript();

        new SectionChooserFormResponder();
        new SectionChooserScript();

        new ScrollTopCookiesScript();

        new ProductViewLogBean();

        $this->setBean(new ProductsBean());

        $this->fields = array(
            "cover_photo"=>"Cover Photo",
            "category_name"=>"Category",
            "brand_name"=>"Brand",
            "product_name"=>"Product Name",

            "sections"=>"Sections",
            "class_name"=>"Class",
            "product_attributes"=>"Attributes",
            "product_variants"=>"Variants",
            "price"=>"Price",
            "promo_price"=>"Promo Price",
            "stock_amount"=>"Stock Amount",
            "visible"=>"Visible",
            //"importID"=>"ImportID"

        );

        $search_fields = array("p.product_name",
            "category_name",
            "class_name",
            "p.brand_name",
            "p.prodID",
            "sections",
            "product_attributes",
            "product_variants",
            "p.price"
            );

        $this->keyword_search->getForm()->getInput("keyword")->getRenderer()->input()->setAttribute("placeholder", "Търси продукт");
        $this->keyword_search->getForm()->setColumns($search_fields);
        $this->keyword_search->setMethod(FormRenderer::METHOD_GET);

        $this->initializeSearchForm();

        foreach ($this->keyword_search->getForm()->inputNames() as $name) {
            $this->getPage()->addParameterName($name);
            $this->getPage()->addParameterName(KeywordSearch::SUBMIT_KEY);
        }

        $qry = $this->bean->query();
        $qry->stmt->columns(
                "p.prodID",
                        "p.product_name",
                        "p.brand_name",
                        "p.visible",
                        "p.price",
                        "p.promo_price",
                        "p.stock_amount",
                        );

        $qry->stmt->alias("(SELECT pp.ppID FROM product_photos pp WHERE pp.prodID = p.prodID ORDER BY pp.position ASC LIMIT 1)", "cover_photo");
        //$qry->stmt->alias("(SELECT group_concat(s.section_title SEPARATOR '<BR>' ) FROM product_sections ps JOIN sections s ON s.secID=ps.secID AND ps.prodID=p.prodID)", "sections");

        $qry->stmt->alias("(SELECT 
        GROUP_CONCAT(CONCAT(a.name,':', cast(pcav.value as char)) ORDER BY a.attrID ASC SEPARATOR '<BR>')
        FROM product_class_attribute_values pcav 
        JOIN product_class_attributes pca ON pca.pcaID = pcav.pcaID 
        JOIN attributes a ON a.attrID = pca.attrID
        WHERE pcav.prodID = p.prodID )", "product_attributes");

        $qry->stmt->alias("(SELECT 
    GROUP_CONCAT(label SEPARATOR '<BR>') FROM 
    (SELECT 
        CONCAT(vo.option_name, ':', GROUP_CONCAT(vo.option_value ORDER BY vo.prodID, vo.pclsID ASC, vo.parentID ASC, vo.position ASC SEPARATOR ';')) as label,
        p1.prodID
        FROM product_variants pv 
        JOIN variant_options vo ON vo.voID = pv.voID 
        JOIN products p1 ON p1.prodID = pv.prodID
    GROUP BY vo.option_name
    ) AS temp WHERE temp.prodID = p.prodID)", "product_variants");




        $qry->stmt->columns("pcls.class_name", "pc.category_name", "pvl.order_counter", "pvl.view_counter");

        $qry->stmt->alias("GROUP_CONCAT(DISTINCT s.section_title SEPARATOR '<BR>')", "sections");

        $qry->stmt->from(" products p ")
            ->leftJoin("product_classes pcls")->on("pcls.pclsID = p.pclsID")
            ->leftJoin("product_categories pc")->on("pc.catID = p.catID")
            ->leftJoin("product_view_log pvl")->on("pvl.prodID = p.prodID")
            ->leftJoin("product_sections ps")->on("ps.prodID = p.prodID")
            ->leftJoin("sections s")->on("s.secID = ps.secID");

        $qry->stmt->group_by = " p.prodID ";


        $this->setIterator($qry);
    }

    protected function initializeSearchForm() : void
    {
        $this->keyword_search->setLayout(FormRenderer::LAYOUT_VBOX);
        $form = $this->keyword_search->getForm();

        $field = DataInputFactory::Create(InputType::NESTED_SELECT, "filter_catID", "Category", 0);
        $field->skip_search_filter_processing = true;

        $bean1 = new ProductCategoriesBean();
        $rend = $field->getRenderer();

        $rend->setIterator(new SelectQuery($bean1->selectTree(array("category_name")), $bean1->key(), $bean1->table()));
        $rend->getItemRenderer()->setValueKey("catID");
        $rend->getItemRenderer()->setLabelKey("category_name");

        if ($rend instanceof SelectField) {
            $rend->setDefaultOption("--- Всички ---");
        }

        //$rend->input()?->setAttribute("onChange", "this.form.requestSubmit()");


        $form->insertInputBefore($field, "keyword");


        $field = DataInputFactory::Create(InputType::SELECT, "filter_brand", "Brand", 0);
        $field->skip_search_filter_processing = true;

        $bean1 = new BrandsBean();
        $rend = $field->getRenderer();

        $rend->setIterator($bean1->query($bean1->key(), "brand_name"));
        $rend->getItemRenderer()->setValueKey("brand_name");
        $rend->getItemRenderer()->setLabelKey("brand_name");

        if ($rend instanceof SelectField) {
            $rend->setDefaultOption("--- Всички ---");
        }

        //$rend->input()?->setAttribute("onChange", "this.form.submit()");


        $form->insertInputBefore($field, "keyword");


        $field = DataInputFactory::Create(InputType::SELECT, "filter_section", "Section", 0);
        $field->skip_search_filter_processing = true;

        $rend = $field->getRenderer();
        $sb = new SectionsBean();

        $rend->setIterator($sb->query($sb->key(),"section_title"));
        $rend->getItemRenderer()->setValueKey("section_title");
        $rend->getItemRenderer()->setLabelKey("section_title");

        if ($rend instanceof SelectField) {
            $rend->setDefaultOption("--- Всички ---");
        }

        //$rend->input()?->setAttribute("onChange", "this.form.submit()");

        $form->insertInputBefore($field, "keyword");


        $field = DataInputFactory::Create(InputType::SELECT, "filter_class", "Product Class", 0);
        $field->skip_search_filter_processing = true;

        $rend = $field->getRenderer();
        $sb = new ProductClassesBean();

        $rend->setIterator($sb->query($sb->key(),"class_name"));
        $rend->getItemRenderer()->setValueKey("class_name");
        $rend->getItemRenderer()->setLabelKey("class_name");

        if ($rend instanceof SelectField) {
            $rend->setDefaultOption("--- Всички ---");
        }

        //$rend->input()?->setAttribute("onChange", "this.form.submit()");

        $form->insertInputBefore($field, "keyword");
    }

    public function processInput() : void
    {
        //parent::processInput();

        $this->keyword_search->processInput();

        if ($this->keyword_search->isProcessed()) {

            $form = $this->keyword_search->getForm();

            if ($form->haveInput("filter_brand")) {
                $filter_brand = $form->getInput("filter_brand")->getValue();
                if ($filter_brand) {
                    $this->query->stmt->where()->match("p.brand_name", $filter_brand);
                }
            }

            if ($form->haveInput("filter_section")) {
                $filter_section = $form->getInput("filter_section")->getValue();
                if ($filter_section) {
                    $this->query->stmt->where()->match("section_title", $filter_section);
//                    $this->query->stmt->having(" sections LIKE :filter_section");
//                    $this->query->stmt->bind(":filter_section", "%$filter_section%");
                }
            }

            if ($form->haveInput("filter_catID")) {
                $filter_catID = $form->getInput("filter_catID")->getValue();
                if ($filter_catID>0) {
                    $this->query->stmt->where()->match("p.catID", $filter_catID);
                }
            }

            if ($form->haveInput("filter_class")) {
                $filter_class = $form->getInput("filter_class")->getValue();
                if ($filter_class) {
                    $this->query->stmt->where()->match("class_name", $filter_class);
//                    $this->query->stmt->bind(":filter_class", );
                }
            }

            if ($form->getInput("keyword")->getValue()) {
                $clauses = $form->prepareClauseCollection("OR");
                $clauses->copyTo($this->query->stmt->where());

            }
        }

        $this->query->stmt->setMeta("ProductListSelect");

        //serialize for product export
        if ($this->view instanceof TableView) {
            $itr = $this->view->getIterator();
            if ($itr instanceof SelectQuery) {
                Session::Set("ProductListSelect", serialize($itr->stmt));
                Debug::ErrorLog("Serialized SQLSelect for this product listing");
            }
        }

    }

    public function initView() : ?Component
    {
        $view = parent::initView();

        $ticr1 = new ImageCell(275, -1);
        $ticr1->setBean(new ProductPhotosBean());
        $ticr1->setLimit(1);
        $view->getColumn("cover_photo")->setCellRenderer($ticr1);

        $view->getColumn("visible")->setCellRenderer(new BooleanCell("Yes", "No"));


        $act = $this->viewItemActions();
        $act->append(Action::RowSeparator());

        $act->append(new Action("Photo Gallery", "gallery/list.php", array(new DataParameter("prodID", $this->bean->key()))));
        $act->append(Action::RowSeparator());

        $act->append(new Action("Sections", "javascript:showSectionChooserForm(%prodID%)", array(new DataParameter("prodID", $this->bean->key()))));

        $act->append(Action::RowSeparator());
        $act->append(new Action("Options", "../options/list.php", array(new DataParameter("prodID"))));


        $act->append(Action::RowSeparator());
        $act->append(new Action("Variants", "variants/list.php", array(new DataParameter("prodID"))));

        return $view;
    }
}

$template = new ProductsList();
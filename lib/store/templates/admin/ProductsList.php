<?php
include_once("templates/admin/BeanListPage.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/ProductClassesBean.php");
include_once("store/beans/ProductSectionsBean.php");
include_once("store/beans/BrandsBean.php");
include_once("store/responders/json/SectionChooserFormResponder.php");
include_once("store/responders/json/ImportUpdateFormResponder.php");
include_once("store/utils/DownloadCSVProducts.php");
include_once("components/PageScript.php");
include_once("store/beans/ProductViewLogBean.php");

class ScrollTopCookiesScript extends PageScript
{
    public function code() : string
    {
        return <<<JS
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
    }
}

class SectionChooserScript extends PageScript
{
    public function code() : string
    {
        return <<<JS
        function showSectionChooserForm(prodID)
        {
            let section_chooser = new JSONFormDialog();
            section_chooser.setTitle("Изберете секции: ");
            section_chooser.setResponder("SectionChooserFormResponder");
            section_chooser.getJSONRequest().setParameter("prodID", prodID);
            section_chooser.show();
        }
JS;
    }
}

class ImportUpdateScript extends PageScript
{
    public function code() : string
    {
        return <<<JS
        function showImportUpdateDialog()
        {
            let import_dialog = new JSONFormDialog();
            import_dialog.setTitle("Изберете файл: ");
            import_dialog.setResponder("ImportUpdateFormResponder");
            import_dialog.show();
        }
JS;
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
        $this->keyword_search->getForm()->getRenderer()->setMethod(FormRenderer::METHOD_GET);

        $this->initializeSearchForm();

        foreach ($this->keyword_search->getForm()->inputNames() as $name) {
            $this->getPage()->addParameterName($name);
            $this->getPage()->addParameterName(KeywordSearch::SUBMIT_KEY);
        }

        $qry = $this->bean->query();
        $qry->select->fields()->set(
                "p.prodID",
                        "p.product_name",
                        "p.brand_name",
                        "p.visible",
                        "p.price",
                        "p.promo_price",
                        "p.stock_amount",
                        );

        $qry->select->fields()->setExpression("(SELECT pp.ppID FROM product_photos pp WHERE pp.prodID = p.prodID ORDER BY pp.position ASC LIMIT 1)", "cover_photo");
        $qry->select->fields()->setExpression("(SELECT group_concat(s.section_title SEPARATOR '<BR>' ) FROM product_sections ps JOIN sections s ON s.secID=ps.secID AND ps.prodID=p.prodID)", "sections");

        $qry->select->fields()->setExpression("(SELECT 
        GROUP_CONCAT(CONCAT(a.name,':', cast(pcav.value as char)) ORDER BY a.attrID ASC SEPARATOR '<BR>')
        FROM product_class_attribute_values pcav 
        JOIN product_class_attributes pca ON pca.pcaID = pcav.pcaID 
        JOIN attributes a ON a.attrID = pca.attrID
        WHERE pcav.prodID = p.prodID )", "product_attributes");

        $qry->select->fields()->setExpression("(SELECT 
    GROUP_CONCAT(label SEPARATOR '<BR>') FROM 
    (SELECT 
        CONCAT(vo.option_name, ':', GROUP_CONCAT(vo.option_value ORDER BY vo.prodID, vo.pclsID ASC, vo.parentID ASC, vo.position ASC SEPARATOR ';')) as label,
        p1.prodID
        FROM product_variants pv 
        JOIN variant_options vo ON vo.voID = pv.voID 
        JOIN products p1 ON p1.prodID = pv.prodID
    GROUP BY vo.option_name
    ) AS temp WHERE temp.prodID = p.prodID)", "product_variants");

        $qry->select->fields()->setExpression("(
        SELECT pcls.class_name FROM product_classes pcls WHERE pcls.pclsID = p.pclsID LIMIT 1
        )", "class_name");

        $qry->select->fields()->setExpression("(
        SELECT pc.category_name FROM product_categories pc WHERE pc.catID = p.catID LIMIT 1
        )", "category_name");


        $qry->select->fields()->setExpression("(
        SELECT pvl.view_counter FROM product_view_log pvl WHERE pvl.prodID = p.prodID LIMIT 1
        )", "view_counter");

        $qry->select->fields()->setExpression("(
        SELECT pvl.order_counter FROM product_view_log pvl WHERE pvl.prodID = p.prodID LIMIT 1
        )", "order_counter");

        $qry->select->from = " products p ";


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

        $rend->setIterator(new SQLQuery($bean1->selectTree(array("category_name")), $bean1->key(), $bean1->getTableName()));
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
                    $this->query->select->where()->add("p.brand_name", "'".$filter_brand."'");
                }
            }

            if ($form->haveInput("filter_section")) {
                $filter_section = $form->getInput("filter_section")->getValue();
                if ($filter_section) {
                    $this->query->select->having = " sections LIKE '%{$filter_section}%' ";
                }
            }

            if ($form->haveInput("filter_catID")) {
                $filter_catID = $form->getInput("filter_catID")->getValue();
                if ($filter_catID>0) {
                    $this->query->select->where()->add("p.catID", $filter_catID);
                }
            }

            if ($form->haveInput("filter_class")) {
                $filter_class = $form->getInput("filter_class")->getValue();
                if ($filter_class) {
                    $this->query->select->having = " class_name = '$filter_class'";
                }
            }

            if ($form->getInput("keyword")->getValue()) {
                $clauses = $form->prepareClauseCollection("OR");
                $this->query->select->having = $clauses->getSQL("");
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

?>

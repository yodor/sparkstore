<?php
include_once("utils/ImageSizes.php");
include_once("templates/content/BeanList.php");

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
include_once("store/utils/CatalogParts.php");

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
        $title = json_encode(tr("Choose sections"), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
        $code = <<<JS
        function showSectionChooserForm(prodID)
        {
            let section_chooser = new JSONFormDialog();
            section_chooser.setTitle($title);
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
        $title = json_encode(tr("Choose a file"), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
        $code = <<<JS
        function showImportUpdateDialog()
        {
            let import_dialog = new JSONFormDialog();
            import_dialog.setTitle($title);
            import_dialog.setResponder("ImportUpdateFormResponder");
            import_dialog.show();
        }
JS;
        $this->setCode($code);
        parent::finalize();
    }
}

class ProductsList extends BeanList {

    /**
     * The list columns of each catalog part
     * @return array<string, string[]>
     */
    public static function PartFields() : array
    {
        return array(
            CatalogParts::BRANDS => array("brand_name"),
            CatalogParts::SECTIONS => array("sections"),
            CatalogParts::CLASSES => array("class_name", "product_attributes"),
            CatalogParts::VARIANTS => array("product_variants"),
        );
    }

    protected DownloadCSVProducts $dcsv_responder;
    protected ImportUpdateFormResponder $import_responder;

    /**
     * The columns without those of the disabled catalog parts
     * @param array<string, string> $fields name => title
     * @return array<string, string>
     */
    public static function EnabledFields(array $fields) : array
    {
        foreach (ProductsList::PartFields() as $part => $names) {
            if (CatalogParts::Enabled($part)) continue;
            foreach ($names as $name) unset($fields[$name]);
        }
        return $fields;
    }

    /**
     * The columns a site chooses (listFields) - those of a disabled catalog part are left out
     * @param array $list_fields
     * @return void
     */
    public function setListFields(array $list_fields) : void
    {
        parent::setListFields(ProductsList::EnabledFields($list_fields));
    }

    public function fillPageActions(ActionCollection $collection): void
    {
        parent::fillPageActions($collection);

        $typeNames = $this->dcsv_responder->getProcessorTypes();

        foreach ($typeNames as $idx=>$typeName) {
            $action = $this->dcsv_responder->createAction($typeName);
            $collection->append($action);
        }

        $action = new Action("import_update");
        $action->getURL()->fromString("javascript:showImportUpdateDialog()");
        $action->setTooltip(tr("Import product data from external edit"));
        $collection->append($action);
    }

    public function __construct()
    {

        parent::__construct();

        $this->dcsv_responder = new DownloadCSVProducts();
        $this->import_responder = new ImportUpdateFormResponder();

        new ImportUpdateScript();

        if (CatalogParts::Enabled(CatalogParts::SECTIONS)) {
            new SectionChooserFormResponder();
            new SectionChooserScript();
        }

        new ScrollTopCookiesScript();

        new ProductViewLogBean();

        $this->setBean(new ProductsBean());

        //the columns - those of a disabled catalog part are left out (CatalogParts); a site chooses and orders them
        //with listFields
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
        );
        $this->fields = ProductsList::EnabledFields($this->fields);

        $search_fields = array("p.product_name", "category_name", "p.prodID", "p.price");
        if (CatalogParts::Enabled(CatalogParts::CLASSES)) $search_fields[] = "class_name";
        if (CatalogParts::Enabled(CatalogParts::BRANDS)) $search_fields[] = "p.brand_name";
        if (CatalogParts::Enabled(CatalogParts::SECTIONS)) $search_fields[] = "section_title";

        $this->search->getForm()->getInput("keyword")->getRenderer()->input()->setAttribute("placeholder", tr("Search a product"));
        $this->search->getForm()->setColumns($search_fields);
        $this->search->getForm()->getRenderer()->setMethod(FormRenderer::METHOD_GET);

        $this->initializeSearchForm();

        //TODO
//        foreach ($this->search->getForm()->inputNames() as $name) {
//            $this->getPage()->addParameterName($name);
//            $this->getPage()->addParameterName(KeywordSearch::SUBMIT_KEY);
//        }

        $qry = $this->bean->query();
        $qry->stmt->columns(
            "p.prodID",
            "p.product_name",
            "p.visible",
            "p.price",
            "p.promo_price",
            "p.stock_amount",
            "p.insert_date",
            "p.update_date",
            "p.catID",
        );

        $qry->stmt->alias("(SELECT pp.ppID FROM product_photos pp WHERE pp.prodID = p.prodID ORDER BY pp.position ASC LIMIT 1)", "cover_photo");

        $qry->stmt->columns("pc.category_name", "pvl.order_counter", "pvl.view_counter");

        $qry->stmt->from(" products p ")
            ->leftJoin("product_categories pc")->on("pc.catID = p.catID")
            ->leftJoin("product_view_log pvl")->on("pvl.prodID = p.prodID");

        if (CatalogParts::Enabled(CatalogParts::BRANDS)) {
            $qry->stmt->columns("p.brand_name");
        }

        if (CatalogParts::Enabled(CatalogParts::CLASSES)) {
            $qry->stmt->columns("pcls.class_name");
            $qry->stmt->from()->leftJoin("product_classes pcls")->on("pcls.pclsID = p.pclsID");

            $qry->stmt->alias("(SELECT 
            GROUP_CONCAT(CONCAT(a.name,':', cast(pcav.value as char)) ORDER BY a.attrID ASC SEPARATOR '<BR>')
            FROM product_class_attribute_values pcav 
            JOIN product_class_attributes pca ON pca.pcaID = pcav.pcaID 
            JOIN attributes a ON a.attrID = pca.attrID
            WHERE pcav.prodID = p.prodID )", "product_attributes");
        }

        if (CatalogParts::Enabled(CatalogParts::VARIANTS)) {
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
        }

        if (CatalogParts::Enabled(CatalogParts::SECTIONS)) {
            //the sections of a product, and section_title for the section filter and the keyword search
            $qry->stmt->alias("GROUP_CONCAT(DISTINCT s.section_title SEPARATOR '<BR>')", "sections");
            $qry->stmt->from()
                ->leftJoin("product_sections ps")->on("ps.prodID = p.prodID")
                ->leftJoin("sections s")->on("s.secID = ps.secID");
            $qry->stmt->group_by = " p.prodID ";
        }

        $this->setIterator($qry);
    }


    protected function initializeSearchForm() : void
    {
        $this->search->setLayout(FormRenderer::LAYOUT_VBOX);
        $form = $this->search->getForm();

        $field = DataInputFactory::Create(InputType::NESTED_SELECT, "filter_catID", "Category", 0);
        $field->skip_search_filter_processing = true;

        $bean1 = new ProductCategoriesBean();
        $rend = $field->getRenderer();

        $rend->setIterator(new SelectQuery($bean1->selectTree(array("category_name")), $bean1->key(), $bean1->table()));
        $rend->getItemRenderer()->setValueKey("catID");
        $rend->getItemRenderer()->setLabelKey("category_name");

        if ($rend instanceof SelectField) {
            $rend->setDefaultOption("--- All ---");
        }

        //$rend->input()?->setAttribute("onChange", "this.form.requestSubmit()");


        $form->insertInputBefore($field, "keyword");


        if (CatalogParts::Enabled(CatalogParts::BRANDS)) {
            $field = DataInputFactory::Create(InputType::SELECT, "filter_brand", "Brand", 0);
            $field->skip_search_filter_processing = true;

            $bean1 = new BrandsBean();
            $rend = $field->getRenderer();

            $rend->setIterator($bean1->query($bean1->key(), "brand_name"));
            $rend->getItemRenderer()->setValueKey("brand_name");
            $rend->getItemRenderer()->setLabelKey("brand_name");

            if ($rend instanceof SelectField) {
                $rend->setDefaultOption("--- All ---");
            }

            //$rend->input()?->setAttribute("onChange", "this.form.submit()");


            $form->insertInputBefore($field, "keyword");
        }

        if (CatalogParts::Enabled(CatalogParts::SECTIONS)) {
            $field = DataInputFactory::Create(InputType::SELECT, "filter_section", "Section", 0);
            $field->skip_search_filter_processing = true;

            $rend = $field->getRenderer();
            $sb = new SectionsBean();

            $rend->setIterator($sb->query($sb->key(),"section_title"));
            $rend->getItemRenderer()->setValueKey("section_title");
            $rend->getItemRenderer()->setLabelKey("section_title");

            if ($rend instanceof SelectField) {
                $rend->setDefaultOption("--- All ---");
            }

            //$rend->input()?->setAttribute("onChange", "this.form.submit()");

            $form->insertInputBefore($field, "keyword");
        }

        if (CatalogParts::Enabled(CatalogParts::CLASSES)) {
            $field = DataInputFactory::Create(InputType::SELECT, "filter_class", "Product Class", 0);
            $field->skip_search_filter_processing = true;

            $rend = $field->getRenderer();
            $sb = new ProductClassesBean();

            $rend->setIterator($sb->query($sb->key(),"class_name"));
            $rend->getItemRenderer()->setValueKey("class_name");
            $rend->getItemRenderer()->setLabelKey("class_name");

            if ($rend instanceof SelectField) {
                $rend->setDefaultOption("--- All ---");
            }

            //$rend->input()?->setAttribute("onChange", "this.form.submit()");

            $form->insertInputBefore($field, "keyword");
        }
    }

    public function processInput() : void
    {
        //parent::processInput();

        $this->search->processInput();

        if ($this->search->isProcessed()) {

            $form = $this->search->getForm();

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
        if ($this->cmp instanceof TableView) {
            $itr = $this->cmp->getIterator();
            if ($itr instanceof SelectQuery) {
                Session::Set("ProductListSelect", serialize($itr->stmt));
                Debug::ErrorLog("Serialized SQLSelect for this product listing");
            }
        }

    }

    protected function initItemActions(ActionCollection $act): void
    {
        parent::initItemActions($act);

        $act->append(Action::RowSeparator());

        $actGallery = TemplateBeanContent::CreateAction("Photo Gallery", tr("Photo Gallery"), "gallery/");
        $actGallery->getURL()->add(new DataParameter("prodID"));
        $act->append($actGallery);

        if (CatalogParts::Enabled(CatalogParts::SECTIONS)) {
            $act->append(Action::RowSeparator());

            $actSections = new Action("Sections", "javascript:showSectionChooserForm(%prodID%)", array(new DataParameter("prodID", $this->bean->key())));
            $actSections->setContents(tr("Sections"));
            $act->append($actSections);
        }

        if (CatalogParts::Enabled(CatalogParts::VARIANTS)) {
            $act->append(Action::RowSeparator());

            $actOptions = TemplateBeanContent::CreateAction("Options", tr("Options"), "/store/options/");
            $actOptions->getURL()->add(new DataParameter("prodID"));
            $act->append($actOptions);

            $act->append(Action::RowSeparator());

            $actVariants = TemplateBeanContent::CreateAction("Variants", tr("Variants"), "variants/");
            $actVariants->getURL()->add(new DataParameter("prodID"));
            $act->append($actVariants);
        }
    }

    public function initialize() : void
    {
        parent::initialize();

        $tableView = $this->tableView();

        $ticr1 = new ImageCell(ImageSize::Small);
        $ticr1->setBean(new ProductPhotosBean());
        $ticr1->setLimit(1);
        $tableView->getColumn("cover_photo")->setCellRenderer($ticr1);

        $tableView->getColumn("visible")->setCellRenderer(new BooleanCell("Yes", "No"));

    }
}
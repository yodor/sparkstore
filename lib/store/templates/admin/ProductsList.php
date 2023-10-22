<?php
include_once("templates/admin/BeanListPage.php");
include_once("store/beans/ProductsBean.php");
include_once("store/beans/ProductPhotosBean.php");
include_once("store/beans/ProductCategoriesBean.php");
include_once("store/beans/ProductClassesBean.php");
include_once("store/beans/ProductSectionsBean.php");
include_once("store/beans/BrandsBean.php");
include_once("store/responders/json/SectionChooserFormResponder.php");
include_once("store/beans/SellableProducts.php");
include_once("store/utils/SellableItem.php");

class DownloadCSVResponder extends RequestResponder
{

    const COMMAND = "download_csv";
    const FILENAME = "catalog_products.csv";

    public function __construct()
    {
        parent::__construct(self::COMMAND);
    }

    public function createAction($title = FALSE, $href = FALSE, $check_code = NULL, $data_parameters = array())
    {
        $action = new Action(self::COMMAND, "?cmd=".self::COMMAND);
        $action->setTooltipText("Download CSV");
        return $action;
    }

    protected function processImpl()
    {
        //clear rendered state of startRender from SparkPage
        ob_end_clean();

        header( "Content-Type: text/csv" );
        header( "Content-Disposition: attachment;filename=".self::FILENAME);
        $fp = fopen("php://output", "w");

        $keys = array("id", "content_id", "title", "description", "availability", "condition", "link", "image_link", "brand", "product_type", "price");

        fputcsv($fp, $keys);

        $bean = new SellableProducts();

        $query = $bean->query("prodID");
        $query->select->group_by = " prodID ";
        $query->select->order_by = " update_date DESC ";

        $query->exec();


        $cats = new ProductCategoriesBean();


        while ($result = $query->nextResult()) {

            $prodID = $result->get("prodID");

            $item = SellableItem::Load($prodID);

            $export_row = array();
            $export_row["id"] = $prodID;
            $export_row["content_id"] = $prodID;
            $export_row["title"] = $item->getTitle();
            $export_row["description"] = $item->getDescription();
            $export_row["availability"] = "in stock";
            $export_row["condition"] = "new";

            $link = LOCAL."/products/details.php?prodID=$prodID";
            $export_row["link"] = fullURL($link);

            $export_row["image_link"] = "";
            if ($item->getMainPhoto() instanceof StorageItem) {
                $image_link = $item->getMainPhoto()->hrefImage(640,-1);
                $export_row["image_link"] = fullURL($image_link);
            }
            $export_row["brand"] = $item->getBrandName();
            $export_row["product_type"] = $cats->getValue($item->getCategoryID(), "category_name");

            $export_row["price"] = $item->getPriceInfo()->getSellPrice();

            fputcsv($fp, $export_row);

        }

        exit;
    }

    protected function parseParams()
    {

    }
}

class ProductFilterInputForm extends InputForm {
    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::NESTED_SELECT, "filter_catID", "Category", 0);
        $bean1 = new ProductCategoriesBean();
        $rend = $field->getRenderer();

        $rend->setIterator(new SQLQuery($bean1->selectTree(array("category_name")), $bean1->key(), $bean1->getTableName()));
        $rend->getItemRenderer()->setValueKey("catID");
        $rend->getItemRenderer()->setLabelKey("category_name");

        $field->getRenderer()->na_label = "--- Всички ---";

        $field->getRenderer()->setInputAttribute("onChange", "this.form.submit()");


        $this->addInput($field);


        $field = DataInputFactory::Create(DataInputFactory::SELECT, "filter_brand", "Brand", 0);
        $bean1 = new BrandsBean();
        $rend = $field->getRenderer();

        $rend->setIterator($bean1->query($bean1->key(), "brand_name"));
        $rend->getItemRenderer()->setValueKey("brand_name");
        $rend->getItemRenderer()->setLabelKey("brand_name");

        $field->getRenderer()->na_label = "--- Всички ---";

        $field->getRenderer()->setInputAttribute("onChange", "this.form.submit()");


        $this->addInput($field);


        $field = DataInputFactory::Create(DataInputFactory::SELECT, "filter_section", "Section", 0);
        $rend = $field->getRenderer();
        $sb = new SectionsBean();

        $rend->setIterator($sb->query($sb->key(),"section_title"));
        $rend->getItemRenderer()->setValueKey("section_title");
        $rend->getItemRenderer()->setLabelKey("section_title");

        $field->getRenderer()->na_label = "--- Всички ---";

        $field->getRenderer()->setInputAttribute("onChange", "this.form.submit()");

        $this->addInput($field);


        $field = DataInputFactory::Create(DataInputFactory::SELECT, "filter_class", "Product Class", 0);
        $rend = $field->getRenderer();
        $sb = new ProductClassesBean();

        $rend->setIterator($sb->query($sb->key(),"class_name"));
        $rend->getItemRenderer()->setValueKey("class_name");
        $rend->getItemRenderer()->setLabelKey("class_name");

        $field->getRenderer()->na_label = "--- Всички ---";

        $field->getRenderer()->setInputAttribute("onChange", "this.form.submit()");

        $this->addInput($field);
    }

}

class PageScript extends Component implements IPageComponent
{
    public function __construct()
    {
        parent::__construct();
    }
    public function startRender()
    {
    }
    protected function renderImpl()
    {
        ?>
        <script type="text/javascript">

            $("[action='Edit']").on("click", function(e){

                let scrollTop = $(window).scrollTop();
                Cookies.set('scrollTop', scrollTop , { expires: 365 });

            });
            let scrollTop = Cookies.get('scrollTop');
            if (scrollTop) {
                window.scrollTo(0, scrollTop);
                Cookies.remove('scrollTop');
            }

            function showSectionChooserForm(prodID)
            {
                let section_chooser = new JSONFormDialog();
                section_chooser.caption="Изберете секции";
                section_chooser.setResponder("SectionChooserFormResponder");
                section_chooser.getJSONRequest().setParameter("prodID", prodID);
                section_chooser.show();
            }
        </script>
        <?php
    }
    public function finishRender()
    {
    }

}

class ProductsList extends BeanListPage
{
    protected $filtersForm;

    public function __construct()
    {
        parent::__construct();
        $this->getPage()->setPageMenu(TemplateFactory::MenuForPage("ProductsList"));

        //$action = new Action("download_csv", "fbexport.php");
        //$action->setTooltipText("Download CSV");
        $dcsv_responder = new DownloadCSVResponder();
        $action = $dcsv_responder->createAction();
        $this->getPage()->getActions()->append($action);

        $responder = new SectionChooserFormResponder();
        $chooser_script = new PageScript();

        $this->setBean(new ProductsBean());

        $this->fields = array(
            "cover_photo"=>"Cover Photo",
            "category_name"=>"Category",
            "brand_name"=>"Brand",
            "product_name"=>"Product Name",

            "sections"=>"Sections",
            "class_name"=>"Class",
            "class_attributes"=>"Attributes",
            "product_variants"=>"Variants",
            "price"=>"Price",
            "promo_price"=>"Promo Price",
            "stock_amount"=>"Stock Amount",
            "visible"=>"Visible",
            //"importID"=>"ImportID"

        );

        $search_fields = array("product_name", "category_name", "class_name",  "keywords", "brand_name", "prodID", "importID" );

        $this->keyword_search->getForm()->setFields($search_fields);
        $this->keyword_search->getForm()->getRenderer()->setMethod(FormRenderer::METHOD_GET);

        $qry = $this->bean->query();


        $this->filtersForm = new ProductFilterInputForm();
        $frend = new FormRenderer($this->filtersForm);
        $frend->getSubmitLine()->setEnabled(false);
        $frend->setMethod(FormRenderer::METHOD_GET);
        $frend->setAttribute("autocomplete", "off");
        $this->append($frend);




        $qry->select->fields()->set(
                "p.prodID",
                        "p.product_name",
                        "pcls.class_name",
                        "p.brand_name",
                        "pc.category_name",
                        "p.visible",
                        "p.price",
                        "p.promo_price",
                        "p.stock_amount",
                        "p.importID");

        $qry->select->fields()->setExpression("(SELECT pp.ppID FROM product_photos pp WHERE pp.prodID = p.prodID ORDER BY pp.position ASC LIMIT 1)", "cover_photo");
        $qry->select->fields()->setExpression("(SELECT group_concat(s.section_title SEPARATOR '<BR>' ) FROM product_sections ps JOIN sections s ON s.secID=ps.secID AND ps.prodID=p.prodID)", "sections");

        $qry->select->fields()->setExpression("(SELECT 
        GROUP_CONCAT(CONCAT(a.name,':', cast(pcav.value as char)) ORDER BY a.attrID ASC SEPARATOR '<BR>')
        FROM product_class_attribute_values pcav 
        JOIN product_class_attributes pca ON pca.pcaID = pcav.pcaID 
        JOIN attributes a ON a.attrID = pca.attrID
        WHERE pcav.prodID = p.prodID )", "class_attributes");

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

        $qry->select->from = " products p JOIN product_categories pc ON pc.catID=p.catID LEFT JOIN product_classes pcls ON pcls.pclsID=p.pclsID";

        $qry->select->group_by = "  p.prodID ";

        $this->setIterator($qry);
    }

    public function getFilterForm() : ProductFilterInputForm
    {
        return $this->filtersForm;
    }

    public function processInput()
    {
        parent::processInput();

        $proc = new FormProcessor();
        $form = $this->filtersForm;

        $proc->process($form);

        if ($proc->getStatus() === IFormProcessor::STATUS_OK) {
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
                    $this->query->select->where()->add("pcls.class_name", "'" . $filter_class . "'");
                }
            }
        }

    }

    public function initView() : TableView
    {
        $view = parent::initView();

        $ticr1 = new ImageCellRenderer(-1, 64);
        $ticr1->setBean(new ProductPhotosBean());
        $ticr1->setLimit(1);
        $view->getColumn("cover_photo")->setCellRenderer($ticr1);

        $view->getColumn("visible")->setCellRenderer(new BooleanCellRenderer("Yes", "No"));


        $act = $this->viewItemActions();
        $act->append(new RowSeparator());

        $act->append(new Action("Photo Gallery", "gallery/list.php", array(new DataParameter("prodID", $this->bean->key()))));
        $act->append(new RowSeparator());

        $act->append(new Action("Sections", "javascript:showSectionChooserForm(%prodID%)", array(new DataParameter("prodID", $this->bean->key()))));

        $act->append(new RowSeparator());
        $act->append(new Action("Options", "../options/list.php", array(new DataParameter("prodID"))));


        $act->append(new RowSeparator());
        $act->append(new Action("Variants", "variants/list.php", array(new DataParameter("prodID"))));

        return $view;
    }

}

$template = new ProductsList();

?>

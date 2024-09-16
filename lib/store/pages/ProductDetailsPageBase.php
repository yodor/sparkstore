<?php
include_once("store/pages/ProductPageBase.php");
include_once("store/components/ProductsTape.php");

include_once("store/utils/PriceInfo.php");
include_once("store/utils/SellableItem.php");


class ProductDetailsPageBase extends ProductPageBase
{

    protected SellableItem $sellable;

    protected ProductsTape $tape;

    public function __construct()
    {
        //should reach storepage constructor to initialize defaults
        //is sellabledataparaser is customizable
        parent::__construct();

        $this->head()->addCSS(STORE_LOCAL . "/css/product_details.css");

        $prodID = -1;
        if (isset($_GET["prodID"])) {
            $prodID = (int)$_GET["prodID"];
        }

        try {
            $this->bean = new SellableProducts();
            //needs the default parser set correctly
            $this->sellable = SellableItem::Load($prodID);
        }
        catch (Exception $e) {
            Session::set("alert", "Този продукт е недостъпен. Грешка: " . $e->getMessage());
            header("Location: list.php");
            exit;
        }

        $this->section = "";

        $catID = $this->sellable->getCategoryID();
        $this->loadCategoryPath($catID);

        $this->prepareKeywords();
        $this->prepareDescription();

        $main_photo = $this->sellable->getMainPhoto();
        if ($main_photo instanceof StorageItem) {
            $this->head()->addOGTag("image", fullURL($this->sellable->getMainPhoto()->hrefImage(600, -1)));

            $this->head()->addOGTag("image:height", "600");
            $this->head()->addOGTag("image:width", "600");
            $this->head()->addOGTag("image:alt", $this->sellable->getTitle());
        }

        $this->updateViewCounter();

        $this->tape = new ProductsTape();

        $this->head()->addCanonicalParameter("prodID");

    }

    /**
     * Use the product name as page title tag
     * @return void
     */
    protected function constructTitle() : void
    {
        $this->setTitle($this->sellable->getTitle());
    }

    /**
     * @return string
     */
    protected function prepareKeywords() : string
    {
        $catID = $this->sellable->getCategoryID();

        //use keywords of the sellable if set
        $keywords = sanitizeKeywords($this->sellable->getKeywords());
        if (mb_strlen($keywords)>0) {
            $this->keywords = $keywords;
            return $keywords;
        }

        //no keywords added for this sellable. try category keywords if any
        $this->loadCategoryPath($catID);
        $keywords = parent::prepareKeywords();
        //no category keywords were set, use the sellable title as keywords
        if (mb_strlen($keywords)<1) {
            $keywords = $this->sellable->getTitle();
        }

        $this->keywords = $keywords;
        return $keywords;
    }

    protected function prepareDescription(): string
    {
        $description = "";

        if ($this->sellable->getDescription()) {
            $description = $this->sellable->getDescription();
        }
        else {
            $description = $this->sellable->getTitle();
        }

        $description = trim($description);
        if ($description) {
            $description = prepareMeta($description);
            $this->description = $description;
        }

        return $description;
    }

    public function getSellable(): SellableItem
    {
        return $this->sellable;
    }

    protected function updateViewCounter()
    {
        $sql = new SQLUpdate();
        $sql->from = "products p";
        $sql->set("p.view_counter", "p.view_counter+1");
        $sql->where()->add("p.prodID", $this->sellable->getProductID());

        $db = DBConnections::Open();
        try {
            $db->transaction();
            $db->query($sql->getSQL());
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollback();
            debug("Unable to increment view count: ".$db->getError());
        }
    }

    protected function selectActiveMenu()
    {
        //$this->selectActiveMenus = false;
        $main_menu = $this->menu_bar->getMainMenu();
        $main_menu->unselectAll();

        $items = $main_menu->getMenuItems();
        foreach ($items as $idx => $item) {
            if ($item instanceof MenuItem) {
                if (strcmp($item->getName(), $this->section) == 0) {
                    $main_menu->setSelectedItem($item);
                }
            }
        }
        $main_menu->constructSelectedPath();
    }

    public function renderSameCategoryProducts(int $limit = 4)
    {
        $catID = (int)$this->sellable->getCategoryID();

        $title = tr("Още продукти от тази категория");

        $select = clone $this->bean->select();
        $select->fields()->setPrefix("sellable_products");
        $select = $this->product_categories->selectChildNodesWith($select, "sellable_products", $catID, array());

//        echo $select->getSQL();
        $qry = new SQLQuery($select, "prodID");

//        $qry = $this->bean->queryFull();
//        $qry->select->where()->add("catID", $catID);
        $qry->select->where()->add("stock_amount" , "0", " > ");
        $qry->select->order_by = " rand() ";
        $qry->select->group_by = " prodID ";
        $qry->select->limit = "$limit";

        $this->tape->setCaption($title);
        $this->tape->setIterator($qry);

        $this->tape->getCaptionURL()->fromString(LOCAL . "/products/list.php");
        $this->tape->getCaptionURL()->add(new URLParameter("catID", $catID));

        $this->tape->render();
    }

    public function renderOtherProducts(int $limit = 4)
    {
        $title = tr("Други продукти");

        $qry = $this->bean->queryFull();
        $qry->select->order_by = " rand() ";
        $qry->select->group_by = " prodID ";
        $qry->select->where()->add("stock_amount" , "0", " > ");
        $qry->select->limit = "$limit";

        $this->tape->setCaption($title);
        $this->tape->setIterator($qry);
        $this->tape->getCaptionURL()->fromString(LOCAL."/products/list.php");
        $this->tape->render();
    }



}

?>

<?php
include_once("store/pages/ProductPageBase.php");

include_once("store/components/ProductsTape.php");

include_once("store/utils/PriceInfo.php");
include_once("store/utils/SellableItem.php");




class ProductDetailsPageBase extends ProductPageBase
{

    protected $sellable = NULL;

    protected $tape = NULL;

    public function __construct()
    {
        parent::__construct();

        $this->addCSS(STORE_LOCAL . "/css/product_details.css");


        $prodID = -1;
        if (isset($_GET["prodID"])) {
            $prodID = (int)$_GET["prodID"];
        }


        try {

            $this->bean = new SellableProducts();

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

        $description = "";

        if ($this->sellable->getDescription()) {
            $description = $this->sellable->getDescription();
        }

        $description = mb_strtolower(trim(strip_tags($description)));
        if ($description) {
            $this->addMeta("description", prepareMeta($description));
        }

        $this->prepareKeywords();

        $this->addOGTag("title", $this->sellable->getTitle());
        $main_photo = $this->sellable->getMainPhoto();
        if ($main_photo instanceof StorageItem) {
            $this->addOGTag("image", fullURL($this->sellable->getMainPhoto()->hrefImage(600, -1)));

            $this->addOGTag("image:height", "600");
            $this->addOGTag("image:width", "600");
            $this->addOGTag("image:alt", $this->sellable->getTitle());
        }

        $this->updateViewCounter();

        $this->tape = new ProductsTape();


    }

    protected function prepareKeywords()
    {
        $catID = $this->sellable->getCategoryID();

        $keywords = $this->sellable->getKeywords();
        //no keywords added for this sellable. try category keywords if any
        if (strlen(trim($keywords)) == 0) {

            //use product_name by default as keywords
            $keywords = $this->sellable->getTitle();

            $category_keywords = $this->getCategoryKeywords($catID);
            if (mb_strlen($category_keywords)>0) {
                $keywords = $category_keywords;
            }
        }

        //cleanup
        $keywords = str_replace("Етикети: ", "", $keywords);
        $keywords = str_replace("Етикет: ", "", $keywords);
        $keywords = mb_strtolower($keywords);

        if($keywords) {
            $this->addMeta("keywords", prepareMeta($keywords));
        }
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

        $db = DBConnections::Get();
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

    protected function constructTitleArray(): array
    {
        $title = parent::constructTitleArray();
        $title[] = $this->sellable->getTitle();
        return $title;
    }

    protected function selectActiveMenu()
    {
        //$this->selectActiveMenus = false;
        $main_menu = $this->menu_bar->getMainMenu();
        $main_menu->unselectAll();

        $items = $main_menu->getMenuItems();
        foreach ($items as $idx => $item) {
            if (strcmp($item->getTitle(), $this->section) == 0) {
                $main_menu->setSelectedItem($item);
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

        $this->tape->setTitle($title);
        $this->tape->setIterator($qry);
        $action = $this->tape->getTitleAction();
        $action->getURLBuilder()->buildFrom(LOCAL."/products/list.php");
        $action->getURLBuilder()->add(new URLParameter("catID", $catID));

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

        $this->tape->setTitle($title);
        $this->tape->setIterator($qry);
        $action = $this->tape->getTitleAction();
        $action->getURLBuilder()->buildFrom(LOCAL."/products/list.php");
        $this->tape->render();
    }



}

?>
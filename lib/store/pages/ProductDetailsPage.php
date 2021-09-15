<?php
include_once("store/pages/ProductListPage.php");

include_once("store/components/ProductsTape.php");

include_once("store/utils/PriceInfo.php");
include_once("store/utils/SellableItem.php");


class ProductDetailsPage extends ProductListPage
{

    protected $sellable = NULL;

    public function __construct()
    {
        parent::__construct();

        $this->addCSS(STORE_LOCAL . "/css/product_details.css");


        $prodID = -1;
        if (isset($_GET["prodID"])) {
            $prodID = (int)$_GET["prodID"];
        }
        $piID = -1;


        if (isset($_GET["piID"])) {
            $piID = (int)$_GET["piID"];
        }

        try {

            $this->setSellableProducts(new SellableProducts());

            $qry = $this->bean->queryFull();
            $qry->setKey("piID");

            $qry->select->where()->add("prodID", $prodID);

            $qry->select->group_by = "piID";


            $num = $qry->exec();

            if ($num < 1) throw new Exception("Product does not exist or is not accessible now");

            $this->sellable = new SellableItem($prodID);

            while ($item = $qry->nextResult()) {

                if ((int)$piID == (int)$item->get("piID")) {

                    $this->sellable->setInventoryID($piID);
                }

                $this->sellable->addInventoryData($item);


            }

            $this->sellable->finalize();
        }
        catch (Exception $e) {

            Session::set("alert", "Този продукт е недостъпен. Грешка: " . $e->getMessage());
            header("Location: list.php");
            exit;
        }

        $piID = $this->sellable->getActiveInventoryID();

        $this->section = $this->sellable->getData($piID,"section");


        $this->loadCategoryPath($this->sellable->getData($piID,"catID"));

        $description = "";
        if ($this->sellable->getCaption()) {
            $description = $this->sellable->getCaption();
        }
        else if ($this->sellable->getDescription()) {
            $description = $this->sellable->getDescription();
        }
        $description = strip_tags($description);

        $keywords = $this->sellable->getKeywords();
        if (strlen(trim($keywords)) == 0) {
            $keywords = $this->sellable->getData($piID, "category_name");
        }

        $keywords = str_replace("Етикети: ", "", $keywords);
        $keywords = str_replace("Етикет: ", "", $keywords);

        $keywords = strtolower($keywords);

        $this->addMeta("description", prepareMeta($description));
        if($keywords) {
            $this->addMeta("keywords", prepareMeta($keywords));
        }
        $this->addOGTag("title", $this->sellable->getTitle());
        $main_photo = $this->sellable->getMainPhoto();
        if ($main_photo instanceof StorageItem) {
            $this->addOGTag("image", fullURL($this->sellable->getMainPhoto()->hrefImage(600, -1)));

            $this->addOGTag("image:height", "600");
            $this->addOGTag("image:width", "600");
            $this->addOGTag("image:alt", $this->sellable->getTitle());
        }

        $this->updateViewCounter();

    }

    public function getSellable(): SellableItem
    {
        return $this->sellable;
    }

    protected function updateViewCounter()
    {
        $sql = new SQLUpdate();
        $sql->from = "product_inventory pi";
        $sql->set("pi.view_counter", "pi.view_counter+1");
        $sql->where()->add("pi.prodID", $this->sellable->getProductID());
        $sql->where()->add("pi.piID", $this->sellable->getActiveInventoryID());

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

    protected function constructTitle()
    {
        $title = array();
        $title[] = "Продукти";

        $category_path = $this->getCategoryPath();

        foreach ($category_path as $idx => $catinfo) {
            $title[] = $catinfo["category_name"];
        }

        $title[] = $this->sellable->getTitle();
        $this->setTitle(constructSiteTitle($title));
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

    public function renderSameCategoryProducts()
    {
        $catID = (int)$this->sellable->getData($this->sellable->getActiveInventoryID(), "catID");

        $title = tr("Още продукти от тази категория");

        $qry = $this->bean->queryFull();
        $qry->select->where()->add("catID", $catID);
        $qry->select->order_by = " rand() ";
        $qry->select->group_by = " prodID ";
        $qry->select->limit = "4";

        $tape = new ProductsTape($title);
        $tape->setIterator($qry);
        $action = $tape->getTitleAction();
        $action->getURLBuilder()->buildFrom(LOCAL."/products/list.php");
        $action->getURLBuilder()->add(new URLParameter("catID", $catID));

        $tape->render();
    }

    public function renderOtherProducts()
    {
        $title = tr("Други продукти");

        $qry = $this->bean->queryFull();
        $qry->select->order_by = " rand() ";
        $qry->select->group_by = " prodID ";
        $qry->select->limit = "4";


        $tape = new ProductsTape($title);
        $tape->setIterator($qry);
        $action = $tape->getTitleAction();
        $action->getURLBuilder()->buildFrom(LOCAL."/products/list.php");
        $tape->render();
    }


}

?>

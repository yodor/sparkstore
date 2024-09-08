<?php
include_once("class/pages/StorePage.php");

include_once("beans/DynamicPagePhotosBean.php");
include_once("beans/DynamicPagesBean.php");

include_once("beans/MenuItemsBean.php");

class ContentPageBase extends StorePage
{
    protected ?DynamicPagesBean $bean = null;
    protected int $id = -1;
    protected array $page_class = array();

    protected ?RawResult $result = null;
    protected ?SQLQuery $menuQuery = null;

    public function __construct()
    {
        parent::__construct();

        $this->id = -1;
        if (isset($_GET["id"])) {
            $this->id = (int)$_GET["id"];
        }
        elseif (isset($_GET["page_id"])) {
            $this->id = (int)$_GET["page_id"];
        }

//will create menu from all pages containing page_class value in their keywords
//required class!
//footer_page; terms; usage
//footer_page; terms; cookies; page1
//footer_page; terms; cookies; page2
//but ?page_class=footer_page;terms; will output menu for 3 pages
//
//but ?page_class=footer_page;cookies; will output menu for 2 pages
//
        $this->page_class = array();
        if (isset($_GET["page_class"])) {
            $this->page_class = explode(";", DBConnections::Get()->escape($_GET["page_class"]));
            foreach ($this->page_class as $idx=>$class) {
                $this->page_class[$idx] = trim($class);
            }
        }

        try {
            $this->bean = new DynamicPagesBean();

            if (count($this->page_class)==0 && $this->id < 1) throw new Exception("Parameter required");

            $query = $this->bean->query($this->bean->key(), "item_title", "content", "keywords", "item_date");
            $query->select->fields()->setExpression("( photo IS NOT NULL )", "have_photo");
            if ($this->id > 0) {
                $query->select->where()->add($this->bean->key(), $this->id);
            }
            else {
                foreach ($this->page_class as $clsas) {
                    $query->select->where()->add("keywords", "'%$clsas%'", " LIKE ", " AND ");
                }
            }
            $query->select->limit = " 1 ";
            $query->select->order_by = " item_date DESC, {$this->bean->key()} DESC ";
            $num = $query->exec();
            if ($num < 1) throw new Exception("Page not found");
            $this->result = $query->nextResult();
            if (!$this->result) throw new Exception("Unable to query page data");
        }
        catch (Exception $e) {
            Session::SetAlert($e);
            header("Location: home.php");
            exit;
        }


        //direct call from menu - only id is passed for linking dynamic pages to the main menu
        //Like 'About Us' page
        if ($this->id>0 && count($this->page_class)==0) {

        }
        //footer page lists all from specific class and pass their id and class
        else if ($this->id>0 && count($this->page_class)>0) {
            $query->select->where()->removeExpression($this->bean->key());
            $query->select->limit = "";

            $this->menuQuery = new SQLQuery($query->select, $this->bean->key());
        }
        //page_class is listed only
        else if (count($this->page_class)>0) {
            //class from keywords, remove last, list remaining
            $result_class = $this->result->get("keywords");
            $page_class = explode(";", $result_class);
            foreach ($page_class as $idx=>$class) {
                $page_class[$idx] = trim($class);
            }
            //unset the last element
            array_pop($page_class);
            foreach ($page_class as $clsas) {
                $query->select->where()->add("keywords", "'%$clsas%'", " LIKE ", " AND ");
            }
            $query->select->where()->removeExpression($this->bean->key());
            $query->select->limit = "";

            $this->menuQuery = new SQLQuery($query->select, $this->bean->key());
        }

        $this->setTItle($this->result->get("item_title"));
    }

    protected function renderImpl()
    {
        $content = $this->result->get("content");
        $title = $this->result->get("item_title");

        echo "<div class='Caption'>$title</div>";

        if ($this->result->get("have_photo")) {
            echo "<div class='page_photo'>";
            $si = new StorageItem();
            $si->className = get_class($this->bean);
            $si->id = $this->result->get($this->bean->key());
            $src = $si->hrefImage();
            echo "<img src='$src'>";
            echo "</div>";
        }

        echo "<div class='summary'>";
        echo $content;
        echo "</div>";

    }

    protected function renderPageMenu()
    {

        $menu_items = $this->menuQuery->exec();
        if ($menu_items>0) {

            $css_class = implode(" ", $this->page_class);

            echo "<div class='column side page_menu $css_class'>";

            echo "<div class='menu_links'>";
            while ($menuItem = $this->menuQuery->nextResult()) {
                $itemID = (int)$menuItem->get($this->bean->key());
                $itemTitle = $menuItem->get("item_title");
                $page_param = implode(";", $this->page_class);
                echo "<a class='item' href='index.php?id=$itemID&page_class=$page_param' title='".attributeValue($itemTitle)."'>";

                if ($menuItem->get("have_photo")) {
                    echo "<div class='photo'>";
                    $photo_href = StorageItem::Image($itemID, get_class($this->bean));
                    echo "<img src='$photo_href'>";
                    echo "</div>";
                }
                echo "<span>$itemTitle</span>";
                echo "</a>";
            }
            echo "</div>"; //menu_links

            echo "</div>"; //column
        }


    }

    public function startRender()
    {
        parent::startRender();

        $this->renderPageMenu();

        $css_class = implode(" ", $this->page_class);
        echo "<div class='column page_data $css_class'>";
    }

    public function finishRender()
    {
        echo "</div>";
        parent::finishRender();
    }
}
?>

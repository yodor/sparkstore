<?php
include_once("store/components/CategoryNavigation.php");

class SectionNavigation extends CategoryNavigation
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function createListIterator() : SQLQuery
    {
        $select = new SQLSelect();
        $select->fields()->set("secID, section_title");
        $select->fields()->setExpression("(SELECT pcpID FROM product_category_photos pcp WHERE pcp.catID = pc.catID ORDER BY position ASC LIMIT 1)", "pcpID");
        $select->from = " sections ";
        $select->where()->add("home_visible", 1 , " > ");
        $select->order_by = " position ASC ";

        $this->item->setValueKey("secID");
        $this->item->setLabelKey("section_title");

        $this->item->getStorageItem()->className="ProductCategoryPhotosBean";
        $this->item->getStorageItem()->setValueKey("pcpID");

        $this->item->getAction()->setURL(new CategoryURL());

        $query = new SQLQuery($select, "catID");
        $query->setBean(new ProductCategoriesBean());
        return $query;
    }
}
?>
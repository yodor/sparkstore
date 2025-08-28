<?php
include_once("store/components/NavigationList.php");

class CategoryNavigation extends NavigationList
{

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute("aria-label", "Product Categories");
    }


    /**
     * Add group_concat column containing the image IDs and return the column name
     * @param SQLSelect $select
     * @return string
     * @throws Exception
     */
    public function createImagesColumn(SQLSelect $select) : void
    {
        $select->fields()->setExpression(
            "(SELECT 
            GROUP_CONCAT(pcp.pcpID SEPARATOR ',') 
            FROM product_category_photos pcp 
            WHERE pcp.catID = pc.catID 
            ORDER BY position ASC 
            LIMIT {$this->imagesLimit}
            )",
            "category_photos");

        $this->item->getStorageItem()->className=ProductCategoryPhotosBean::class;
        $this->item->getStorageItem()->setValueKey("category_photos");
    }

    public function createListIterator() : SQLQuery
    {
        $bean = new ProductCategoriesBean();

        $select = new SQLSelect();
        $select->fields()->set("pc.catID, pc.category_name");
        $select->from = " product_categories pc ";
        $select->where()->add("pc.parentID", 0);
        $select->order_by = " pc.lft ";

        $this->item->setValueKey($bean->key());
        $this->item->setLabelKey("category_name");

        $this->item->getAction()->setURL(new CategoryURL());

        $query = new SQLQuery($select, $bean->key());
        $query->setBean($bean);
        return $query;
    }

    public function createTapeIterator() : ?SQLQuery
    {
        $tape_select = $this->iterator->bean()->selectChildNodesWith($this->tapeProducts, "sellable_products", $this->item->getValue(), array($this->item->getValueKey(), $this->item->getLabelKey()));
        return new SQLQuery($tape_select, "prodID");
    }

}
?>
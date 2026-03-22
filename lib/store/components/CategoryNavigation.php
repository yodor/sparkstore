<?php
include_once("store/components/NavigationList.php");

class CategoryNavigation extends NavigationList
{

    protected int $parentID = 0;

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute("aria-label", "Product Categories");
    }

    public function setParentID(int $parentID) : void
    {
        $this->parentID = $parentID;
    }
    public function getParentID() : int
    {
        return $this->parentID;
    }

    /**
     * Add group_concat column containing the image IDs and return the column name
     * @param SQLSelect $select
     * @return string
     * @throws Exception
     */
    public function createImagesColumn(SQLSelect $select) : void
    {
        $select->alias(
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

    public function createListIterator() : SelectQuery
    {
        $bean = new ProductCategoriesBean();

        $select = SQLSelect::Table(" product_categories pc ");
        $select->columns("pc.catID", "pc.category_name");
        $select->where()->match("pc.parentID", $this->parentID);
        $select->order("pc.lft", OrderDirection::ASC);

        $this->item->setValueKey($bean->key());
        $this->item->setLabelKey("category_name");

        $this->item->getAction()->setURL(new CategoryURL());

        $query = new SelectQuery($select, $bean->key());
        $query->setBean($bean);
        return $query;
    }

    public function createTapeIterator() : ?SelectQuery
    {
        $tape_select = $this->iterator->bean()->selectChildNodesWith($this->tapeProducts, "sellable_products", $this->item->getValue(), array($this->item->getValueKey(), $this->item->getLabelKey()));
        return new SelectQuery($tape_select, "prodID");
    }

}
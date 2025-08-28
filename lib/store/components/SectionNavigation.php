<?php
include_once("store/components/NavigationList.php");
include_once("store/beans/SectionsBean.php");
include_once("store/beans/SectionBannersBean.php");

class SectionNavigation extends NavigationList
{
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute("aria-label", "Product Sections");
    }

    protected function createImagesColumn(SQLSelect $select): void
    {
        $select->fields()->setExpression(
            "(SELECT 
            GROUP_CONCAT(sb.sbID SEPARATOR ',')  
            FROM section_banners sb 
            WHERE sb.secID = s.secID 
            ORDER BY sb.position ASC LIMIT 4)",
            "section_photos");

        $this->item->getStorageItem()->className=SectionBannersBean::class;
        $this->item->getStorageItem()->setValueKey("section_photos");
    }

    protected function createListIterator() : SQLQuery
    {
        $select = new SQLSelect();
        $select->fields()->set("s.secID, s.section_title");
        $select->from = " sections s";
        $select->where()->add("s.home_visible", 1 );
        $select->order_by = " s.position ASC ";

        $this->item->setValueKey("secID");
        $this->item->setLabelKey("section_title");

        $this->item->getAction()->setURL(new ProductListURL());

        $query = new SQLQuery($select, "secID");
        $query->setBean(new SectionsBean());
        return $query;
    }

    protected function createTapeIterator(): ?SQLQuery
    {
        $sectionName = $this->item->getLabel();
        $this->tapeProducts->where()->clear();
        $this->tapeProducts->where()->add("product_sections", "'$sectionName'", " LIKE ");

        return new SQLQuery($this->tapeProducts, "prodID");
    }


}
?>
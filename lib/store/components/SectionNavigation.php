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

    public function createImagesColumn(SQLSelect $select): void
    {
        $select->fields()->setExpression(
            "(SELECT 
            GROUP_CONCAT( CONCAT(sb.sbID,'|',sb.link) SEPARATOR ',')  
            FROM section_banners sb 
            WHERE sb.secID = s.secID 
            ORDER BY sb.position ASC 
            LIMIT {$this->imagesLimit}
            )",
            "section_photos");

        $this->item->getStorageItem()->className=SectionBannersBean::class;
        $this->item->getStorageItem()->setValueKey("section_photos");
    }

    public function createListIterator() : SQLQuery
    {
        $select = new SQLSelect();
        $select->fields()->set("s.secID, s.section_title");
        $select->from = " sections s";
        $select->where()->add("s.home_visible", 1 );
        $select->order_by = " s.position ASC ";

        $this->item->setValueKey("secID");
        $this->item->setLabelKey("section_title");

        $section_url = new ProductListURL();
        $section_url->add(new DataParameter("section","section_title"));
        $this->item->getAction()->setURL($section_url);

        $query = new SQLQuery($select, "secID");
        $query->setBean(new SectionsBean());
        return $query;
    }

    public function createTapeIterator(): ?SQLQuery
    {
        $sectionName = $this->item->getLabel();
        $this->tapeProducts->where()->removeExpression("product_sections");
        $this->tapeProducts->where()->add("product_sections", "'$sectionName'", " LIKE ");

        return new SQLQuery($this->tapeProducts, "prodID");
    }


}
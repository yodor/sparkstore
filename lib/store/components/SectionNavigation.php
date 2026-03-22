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
        $select->alias(
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

    public function createListIterator() : SelectQuery
    {
        $select = SQLSelect::Table("sections s");
        $select->columns("s.secID", "s.section_title");

        $select->where()->match("s.home_visible", 1 );
        $select->order("s.position", OrderDirection::ASC);

        $this->item->setValueKey("secID");
        $this->item->setLabelKey("section_title");

        $section_url = new ProductListURL();
        $section_url->add(new DataParameter("section","section_title"));
        $this->item->getAction()->setURL($section_url);

        $query = new SelectQuery($select, "secID");
        $query->setBean(new SectionsBean());
        return $query;
    }

    public function createTapeIterator(): ?SelectQuery
    {
        $sectionName = $this->item->getLabel();
        $this->tapeProducts->where()->removeExpression("product_sections");
        $this->tapeProducts->where()->expression("product_sections LIKE :section_name");
        $this->tapeProducts->where()->bind(":section_name", "%$sectionName%");

        return new SelectQuery($this->tapeProducts, "prodID");
    }


}
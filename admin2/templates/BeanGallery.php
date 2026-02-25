<?php
include_once("BeanList.php");
include_once("components/GalleryView.php");
class BeanGallery extends BeanList
{
    public function __construct()
    {
        parent::__construct();
        $this->setName("Photo Gallery");
    }

    public function initialize(): void
    {
        $this->setListFields(array($this->bean->key()=>"ID", "position"=>"Position", "caption"=>"Caption", "date_upload"=>"Date Upload"));

        if ($this->request_condition instanceof BeanKeyCondition) {
            $this->bean->select()->where()->addURLParameter($this->request_condition->getURLParameter());
            $this->query->select->where()->addURLParameter($this->request_condition->getURLParameter());
        }

        $h_delete = new DeleteItemResponder($this->bean);

        $h_repos = new ChangePositionResponder($this->bean);

        $this->cmp = new GalleryView($this->bean, $this->query);

    }
    public function galleryView() : GalleryView
    {
        if ($this->cmp instanceof GalleryView) return $this->cmp;
        throw new Exception("Incorrect component class - expected GalleryView");
    }
}
<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class StoreConfigForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "phone_orders", "Phone Orders", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "tbi_uid", "TBI Store UID", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "facebook_url", "Facebook URL", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "facebook_page_id", "Facebook Page ID", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "instagram_url", "Instagram URL", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "youtube_url", "Youtube URL", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "maps_url", "Google Maps URL", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "phone_text", "Phone Text", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "email_text", "Email Text", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "address_text", "Address Text", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "working_hours_text", "Working hours Text", 0);
        $this->addInput($field);


    }

}

?>
<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class StoreConfigForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "marquee_text", "Home Marquee", 0);
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

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "address", "Address", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "phone", "Phone", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::EMAIL, "email", "Email", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "contacts_summary", "Contacts summary", 0);
        $this->addInput($field);



    }

}

?>
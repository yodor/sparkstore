<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class StoreConfigForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $grp_general = new InputGroup("general", "General");
        $this->addGroup($grp_general);
        $field = DataInputFactory::Create(DataInputFactory::TEXT, "phone_orders", "Phone (For receiving orders)", 0);
        $this->addInput($field, $grp_general);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "tbi_uid", "TBI Store UID", 0);
        $this->addInput($field, $grp_general);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "marquee_text", "Header Marquee Text", 0);
        $this->addInput($field, $grp_general);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "facebook_page_id", "Facebook Page ID (Enable Facebook chat Plugin)", 0);
        $this->addInput($field, $grp_general);

        $grp_footer = new InputGroup("footerButtons", "Site Footer - Round Buttons");
        $this->addGroup($grp_footer);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "facebook_url", "Facebook URL", 0);
        $this->addInput($field,$grp_footer);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "instagram_url", "Instagram URL", 0);
        $this->addInput($field,$grp_footer);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "youtube_url", "Youtube URL", 0);
        $this->addInput($field,$grp_footer);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "maps_url", "Google Maps URL", 0);
        $this->addInput($field,$grp_footer);


        $grp_pagefooter = new InputGroup("pageFooter", "Page Footer - Info Text");
        $this->addGroup($grp_pagefooter);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "phone_text", "Phone Text", 0);
        $this->addInput($field, $grp_pagefooter);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "email_text", "Email Text", 0);
        $this->addInput($field, $grp_pagefooter);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "address_text", "Address Text", 0);
        $this->addInput($field, $grp_pagefooter);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "working_hours_text", "Working hours Text", 0);
        $this->addInput($field, $grp_pagefooter);


    }

}

?>
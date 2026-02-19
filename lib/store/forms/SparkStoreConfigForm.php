<?php
include_once("forms/InputForm.php");
include_once("input/DataInputFactory.php");

class SparkStoreConfigForm extends InputForm
{

    public function __construct()
    {

        parent::__construct();

        $grp_general = new InputGroup("general", "General");
        $this->addGroup($grp_general);

        $field = DataInputFactory::Create(InputType::TEXT, "email_orders", "Email (For receiving orders)", 0);
        $this->addInput($field, $grp_general);

        $field = DataInputFactory::Create(InputType::TEXT, "phone_orders", "Phone (For receiving orders)", 0);
        $this->addInput($field, $grp_general);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "marquee_text", "Header Marquee Text", 0);
        $this->addInput($field, $grp_general);

        $field = DataInputFactory::Create(InputType::SESSION_IMAGE, "home_banner_popup", "Banner Image (Home page banner popup)", 0);
//        $responder = $field->getRenderer()->getResponder();
//        if ($responder instanceof ImageUploadResponder) {
//            $responder->setPhotoSize(-1,256);
//        }
        $this->addInput($field, $grp_general);



        $field = DataInputFactory::Create(InputType::TEXT, "tawkto_id", "Tawk.to Chat Plugin ID", 0);
        $this->addInput($field, $grp_general);
//
        $grp_productInfo = new InputGroup("products", "Products Information");
        $this->addGroup($grp_productInfo);

        $field = DataInputFactory::Create(InputType::MCE_TEXTAREA, "product_list_footer", "All products list footer (When no category is selected)", 0);
        $this->addInput($field, $grp_productInfo);

        $field = DataInputFactory::Create(InputType::MCE_TEXTAREA, "products_howtoorder", "How To Order Description", 0);
        $this->addInput($field,$grp_productInfo);

//
        $grp_footer = new InputGroup("footerButtons", "Site Footer - Round Buttons");
        $this->addGroup($grp_footer);

        $field = DataInputFactory::Create(InputType::TEXT, "facebook_url", "Facebook URL", 0);
        $this->addInput($field,$grp_footer);

        $field = DataInputFactory::Create(InputType::TEXT, "instagram_url", "Instagram URL", 0);
        $this->addInput($field,$grp_footer);

        $field = DataInputFactory::Create(InputType::TEXT, "youtube_url", "Youtube URL", 0);
        $this->addInput($field,$grp_footer);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "maps_url", "Google Maps URL", 0);
        $this->addInput($field,$grp_footer);

//
        $grp_pagefooter = new InputGroup("pageFooter", "Page Footer - Info Text");
        $this->addGroup($grp_pagefooter);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "phone_text", "Phone Text", 0);
        $this->addInput($field, $grp_pagefooter);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "email_text", "Email Text", 0);
        $this->addInput($field, $grp_pagefooter);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "address_text", "Address Text", 0);
        $this->addInput($field, $grp_pagefooter);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "working_hours_text", "Working hours Text", 0);
        $this->addInput($field, $grp_pagefooter);

//
        $grp_tbi = new InputGroup("tbiModule", "TBI Module settings");
        $this->addGroup($grp_tbi);

        $field = DataInputFactory::Create(InputType::TEXT, "tbi_uid", "TBI Store UID", 0);
        $this->addInput($field, $grp_tbi);

        $field = DataInputFactory::Create(InputType::TEXT, "tbi_fusion_style", "TBI Fusion Style", 0);
        $this->addInput($field, $grp_tbi);

        $field = DataInputFactory::Create(InputType::TEXT, "tbi_fusion_script", "TBI Fusion Script", 0);
        $this->addInput($field, $grp_tbi);
//
        $grp_uncr = new InputGroup("uncrModule", "UniCredit Module settings");
        $this->addGroup($grp_uncr);

        $field = DataInputFactory::Create(InputType::TEXT, "uncr_otp_user", "OTP User", 0);
        $this->addInput($field, $grp_uncr);

        $field = DataInputFactory::Create(InputType::TEXT, "uncr_otp_pass", "OTP Pass", 0);
        $this->addInput($field, $grp_uncr);

        $field = DataInputFactory::Create(InputType::TEXT, "uncr_kop", "КОП", 0);
        $this->addInput($field, $grp_uncr);

        $field = DataInputFactory::Create(InputType::CHECKBOX, "uncr_test", "Enable testing environemnt", 0);
        $this->addInput($field, $grp_uncr);

    }

}
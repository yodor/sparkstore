<?php
//the contacts page (ContactsContent): addresses, map, the enquiry dialog
include_once("store/templates/modules/main/content/ContactsContent.php");
$config = ContactsContent::Configure();
$config->title = tr("Contacts");
$config->requireAuth = false;

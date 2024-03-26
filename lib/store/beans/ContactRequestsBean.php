<?php
include_once("beans/DBTableBean.php");

class ContactRequestsBean extends DBTableBean
{
    protected $createString = "CREATE TABLE `contact_requests` (
 `crID` int(10) unsigned NOT NULL auto_increment,
 `fullname` varchar(255) NOT NULL,
 `email` varchar(255) NOT NULL,
 `query` varchar(255) NOT NULL,
 `date_created` timestamp NOT NULL default CURRENT_TIMESTAMP,
 PRIMARY KEY  (`crID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

    public function __construct()
    {
        parent::__construct("contact_requests");

    }

}

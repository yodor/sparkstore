<?php
include_once("beans/DBTableBean.php");

/**
 * The local copy of the offices of the couriers (CourierOffices::Sync - the admin 'Courier Offices' page): offices
 * only (no lockers), the ones accepting cash. 'office_key' is the courier's own id of the office (Speedy: id,
 * Econt: code) - the shipments need it.
 */
class CourierOfficesBean extends DBTableBean
{
    protected string $createString = "CREATE TABLE `courier_offices` (
 `coID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `courier` int(11) unsigned NOT NULL,
 `office_key` varchar(32) NOT NULL,
 `city` varchar(128) NOT NULL,
 `post_code` varchar(16) NOT NULL DEFAULT '',
 `name` varchar(255) NOT NULL,
 `address` varchar(255) NOT NULL DEFAULT '',
 `cash` tinyint(1) NOT NULL DEFAULT 1,
 `updated` timestamp NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`coID`),
 UNIQUE KEY `courier_office` (`courier`, `office_key`),
 KEY `courier_city` (`courier`, `city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    public function __construct()
    {
        parent::__construct("courier_offices");
    }
}

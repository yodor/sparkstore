<?php
include_once("responders/json/ContactRequestFormResponder.php");

/**
 * Contact enquiry of a store site (contacts page): the core enquiry dialog sent to StoreConfig::ORDER_EMAIL -
 * the shop's address from config/store.php or the admin (Content → Config → Base 'email_orders') - with the
 * conversion of Settings > Marketing (the core responder). Nothing is stored.
 */
class StoreContactFormResponder extends ContactRequestFormResponder
{
    protected function recipient(): string
    {
        return (string)Spark::Get(StoreConfig::ORDER_EMAIL);
    }
}

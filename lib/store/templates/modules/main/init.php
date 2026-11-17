<?php
//
// main module - the store part (after the core init.php, before the site init.php): the store page look; the root
// addresses every store site has are the scripts of scripts/ (a site script of the same name replaces them):
//
//  /contacts       contacts page with the enquiry form    scripts/contacts.php
//  /logo           the logo image (the admin upload or the store default)    scripts/logo.php
//  /sitemap.xml    the cached sitemap (createsitemap.php) or the live one; ?live=1 always live    scripts/sitemap.xml.php
//
include_once("store/pages/StoreModulePage.php");

$module = Module::Active();
$module->pageClass = StoreModulePage::class;

//the old root scripts
$module->addRedirect("contacts.php", "contacts");
$module->addRedirect("logo.php", "logo");
$module->addRedirect("sitemap.php", "sitemap.xml");
$module->addRedirect("sitemapcache.php", "sitemap.xml");

<?php
//
// main module - the store part (after the core init.php, before the site init.php): the store page look; the root
// addresses every store site has (a site script or path file of the same name replaces them):
//
//  /contacts       contacts page with the enquiry form    path/contacts.php (ContactsContent)
//  /logo           the logo image (the admin upload or the store default)    scripts/logo.php
//  /sitemap.xml    the cached sitemap (createsitemap.php) or the live one; ?live=1 always live    scripts/sitemap.xml.php
//

$module = Module::Active();
//the site StorePage frame
$module->pageClass = "StorePage";

//the old root scripts
$module->addRedirect("contacts.php", "contacts");
$module->addRedirect("logo.php", "logo");
$module->addRedirect("sitemap.php", "sitemap.xml");
$module->addRedirect("sitemapcache.php", "sitemap.xml");

<?php
//a product: /products/<prodID>/<slug>/ - a site sets its own subclass in its own path/details.php
$config = ProductDetailsContent::Configure();
$config->requireAuth = false;

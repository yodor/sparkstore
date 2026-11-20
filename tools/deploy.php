<?php
/**
 * Deploy a store site - a new store site in an empty folder, or the SparkBox part of an existing site root again
 * (--update). Uses the sparkbox checkout next to this one (or --sparkbox=<path>).
 *
 *   php tools/deploy.php --title="My Shop" --folder=/home/<user>/sites/myshop --db=<name>:<password> --admin=<email>:<password>
 *   php tools/deploy.php --update --folder=/home/<user>/domains/<site>/public_html [--write] [--remove-links]
 *
 * Run it without arguments for all the options (lib/store/deploy/StoreDeployment.php).
 */

if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

$args = array_slice($argv, 1);

//the sparkbox checkout: --sparkbox=<path>, else next to this one
$sparkbox = dirname(__DIR__, 2) . "/sparkbox";
foreach ($args as $arg) {
    if (str_starts_with($arg, "--sparkbox=")) $sparkbox = rtrim(substr($arg, strlen("--sparkbox=")), "/");
}
if (!is_file("$sparkbox/lib/deploy/SiteDeployment.php")) {
    fwrite(STDERR, "sparkbox not found at '$sparkbox' - give it with --sparkbox=<path>\n");
    exit(1);
}

set_include_path("$sparkbox/lib" . PATH_SEPARATOR . dirname(__DIR__) . "/lib" . PATH_SEPARATOR . get_include_path());
include_once("store/deploy/StoreDeployment.php");

$deployment = new StoreDeployment();
$deployment->sparkboxPath = $sparkbox;
if (count($args) < 1) {
    echo $deployment->usage();
    exit(1);
}
try {
    $deployment->parseArguments($args);
}
catch (Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n\n" . $deployment->usage());
    exit(1);
}
exit($deployment->run() ? 0 : 1);

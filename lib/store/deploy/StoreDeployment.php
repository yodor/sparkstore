<?php
include_once("deploy/SiteDeployment.php");

/**
 * The deployment of a store site - the core site (SiteDeployment) with the store layer: the products module, the
 * features of the store (--features, default shop and fast_order: the checkout and the account come with the shop),
 * config/include.php (sparkstore on the include path, the features) and config/store.php, the
 * frame of the store (StoreFrame, set up in the site init class/templates/init.php - no page class of its own),
 * the storefront link. A store site needs its database (--db).
 */
class StoreDeployment extends SiteDeployment
{
    //the sparkstore checkout as the web server sees it (default: this checkout)
    public string $sparkstorePath = "";

    /**
     * The store features of a new site (config/include.php) - --features=shop,fast_order; --features= a catalog
     * @var array<string>
     */
    public array $features = array("shop", "fast_order");

    public function __construct()
    {
        parent::__construct();
        $this->sparkstorePath = (string)realpath(__DIR__ . "/../../..");
    }

    protected function layer() : string
    {
        return "sparkstore";
    }

    protected function pathsLine() : string
    {
        return "sparkbox: $this->sparkboxPath    sparkstore: $this->sparkstorePath    app path: $this->appPath";
    }

    protected function siteKind() : string
    {
        return "store site";
    }

    public function usage() : string
    {
        return parent::usage() . "  [--sparkstore=<path>] - the sparkstore checkout as the web server sees it (default: this one)\n"
            . "  [--features=shop,fast_order] - the store features of a new site (empty: a catalog)\n";
    }

    protected function argument(string $name, ?string $value) : void
    {
        if ($name === "sparkstore") {
            $this->sparkstorePath = rtrim((string)$value, "/");
            return;
        }
        if ($name === "features") {
            $features = array_values(array_filter(array_map("trim", explode(",", (string)$value))));
            foreach ($features as $feature) {
                if (!in_array($feature, array("shop", "fast_order"), true)) throw new Exception("--features: shop, fast_order - '$feature'");
            }
            $this->features = $features;
            return;
        }
        parent::argument($name, $value);
    }

    public function parseArguments(array $args) : void
    {
        parent::parseArguments($args);
        if (!$this->update && $this->dbName === "") throw new Exception("a store site needs its database: --db=<name>:<password>");
    }

    protected function routes() : array
    {
        //checkout and account: routed by the shop feature
        return array_merge(parent::routes(), array("products"));
    }

    protected function frontLinks() : array
    {
        return parent::frontLinks() + array("storefront" => $this->sparkstorePath . "/storefront");
    }

    protected function beanFolders() : array
    {
        return parent::beanFolders() + array($this->sparkstorePath . "/lib/store/beans" => "store/beans/");
    }

        protected function siteFiles() : array
    {
        $files = parent::siteFiles();
        $files["config/include.php"] = $this->includeFile();
        $files["config/store.php"] = $this->storeFile();
        return $files;
    }

    /**
     * config/include.php - sparkstore on the include path (next to sparkbox: a relative path, else the absolute one)
     * and the features of the site
     */
    protected function includeFile() : string
    {
        $sibling = (dirname($this->sparkstorePath) === dirname($this->sparkboxPath));
        $path = $sibling
            ? "Spark::PathParts(Spark::Get(Config::SPARKBOX_PATH), \"..\", " . var_export(basename($this->sparkstorePath), true) . ", \"lib\")"
            : var_export($this->sparkstorePath . "/lib", true);
        return "<?php\n//sparkstore/lib on the include path and in the loader locations (prefix 'store')\n"
            . "Spark::IncludePath($path, \"store\");\n\n//boot sparkstore\ninclude_once(\"sparkstore.php\");\n"
            . "\n//the store features of the site (none: a catalog)\n"
            . ((count($this->features) > 0) ? "Features::Enable(\"" . implode("\", \"", $this->features) . "\");\n" : "//Features::Enable(\"shop\", \"fast_order\");\n");
    }

    /**
     * config/store.php - the store settings (the shop e-mail: the root administrator)
     */
    protected function storeFile() : string
    {
        $lines = array("<?php", "//the store settings of the site - written by sparkstore/tools/deploy.php, change them here",
                       "include_once(\"storeconfig.php\");", "");
        if ($this->adminEmail !== "") {
            $lines[] = "//the shop e-mail: orders, enquiries (the admin value in Content > Config wins when set)";
            $lines[] = "Spark::Set(StoreConfig::ORDER_EMAIL, " . var_export($this->adminEmail, true) . ");";
        }
        else {
            $lines[] = "//Spark::Set(StoreConfig::ORDER_EMAIL, \"orders@example.com\");";
        }
        $lines[] = "Spark::Set(StoreConfig::DEFAULT_CURRENCY, \"EUR\");";
        $lines[] = "Spark::Set(StoreConfig::DEFAULT_CURRENCY_SYMBOL, \"&euro;\");";
        return implode("\n", $lines) . "\n";
    }

    /**
     * The frame of a store site: StorePage of the store modules, set up in the site init - the site css
     */
    protected function frameFiles() : array
    {
        return array("class/templates/init.php" => "<?php\n"
            . "//\n// The site init - every module request, before the init.php of the module: the store setup of the site and\n"
            . "// its frame (StoreFrame - the sections of StorePage: header, menu, footer ...).\n//\n"
            . "include_once(\"store/components/frame/StoreFrame.php\");\n\n"
            . "//the site css (the site layer)\n"
            . "StoreFrame::Stylesheet(Spark::Get(Config::LOCAL) . \"/css/site.css\");\n");
    }
}

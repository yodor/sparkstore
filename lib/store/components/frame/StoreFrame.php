<?php
include_once("store/components/frame/StoreHeader.php");
include_once("store/components/frame/StoreMenu.php");
include_once("store/components/frame/StorePageFooter.php");
include_once("store/components/frame/StoreFooter.php");
include_once("store/components/frame/StoreCookies.php");

/**
 * The set up of the store frame (StorePage) - a site sets it in its site init (class/templates/init.php):
 *
 *  StoreFrame::Stylesheet(Spark::Get(Config::LOCAL) . "/css/store.css");     the site css of the frame
 *  StoreFrame::Header()->setVideo(Spark::Get(Config::LOCAL) . "/images/header_logo");
 *  StoreFrame::Menu()->setCustomerGroup(false);
 *  StoreFrame::Disable(StoreFrame::PAGE_FOOTER);
 *  StoreFrame::HomeAction("Начало", Spark::Get(Config::LOCAL) . "/");        the first breadcrumb item
 *  StoreFrame::Use(StoreFrame::HEADER, MyHeader::class);                     a section of its own (a subclass)
 *
 * The frame of every store module page takes its sections from here.
 */
class StoreFrame
{
    const string HEADER = "header";
    const string MENU = "menu";
    const string PAGE_FOOTER = "page_footer";
    const string FOOTER = "footer";
    const string COOKIES = "cookies";

    /**
     * The section classes
     * @var array<string, string>
     */
    protected static array $classes = array(
        StoreFrame::HEADER => StoreHeader::class,
        StoreFrame::MENU => StoreMenu::class,
        StoreFrame::PAGE_FOOTER => StorePageFooter::class,
        StoreFrame::FOOTER => StoreFooter::class,
        StoreFrame::COOKIES => StoreCookies::class,
    );

    /**
     * The section objects (created on first use)
     * @var array<string, StoreSection>
     */
    protected static array $sections = array();

    /**
     * The sections not shown
     * @var array<string, bool>
     */
    protected static array $disabled = array();

    /**
     * The stylesheets of the site for the frame (after the store css)
     * @var array<string>
     */
    protected static array $stylesheets = array();

    protected static ?array $homeAction = null;

    private function __construct()
    {
    }

    /**
     * The section '$name' - its object for the options (created on first use)
     * @param string $name
     * @return StoreSection
     * @throws Exception
     */
    public static function Section(string $name) : StoreSection
    {
        if (!isset(StoreFrame::$classes[$name])) throw new Exception("Unknown store frame section: $name");
        if (!isset(StoreFrame::$sections[$name])) {
            $class = StoreFrame::$classes[$name];
            StoreFrame::$sections[$name] = new $class();
        }
        return StoreFrame::$sections[$name];
    }

    public static function Header() : StoreHeader
    {
        return StoreFrame::Section(StoreFrame::HEADER);
    }

    public static function Menu() : StoreMenu
    {
        return StoreFrame::Section(StoreFrame::MENU);
    }

    public static function PageFooter() : StorePageFooter
    {
        return StoreFrame::Section(StoreFrame::PAGE_FOOTER);
    }

    public static function Footer() : StoreFooter
    {
        return StoreFrame::Section(StoreFrame::FOOTER);
    }

    public static function Cookies() : StoreCookies
    {
        return StoreFrame::Section(StoreFrame::COOKIES);
    }

    /**
     * A section class of the site - a subclass of the store section (its options are set after this call)
     * @param string $name
     * @param string $className
     * @return void
     * @throws Exception
     */
    public static function Use(string $name, string $className) : void
    {
        if (!isset(StoreFrame::$classes[$name])) throw new Exception("Unknown store frame section: $name");
        if (!is_subclass_of($className, StoreSection::class)) {
            throw new Exception("$className is not a store frame section");
        }
        StoreFrame::$classes[$name] = $className;
        unset(StoreFrame::$sections[$name]);
    }

    /**
     * Do not show the section '$name'
     * @param string $name
     * @return void
     */
    public static function Disable(string $name) : void
    {
        StoreFrame::$disabled[$name] = true;
    }

    public static function IsEnabled(string $name) : bool
    {
        return !isset(StoreFrame::$disabled[$name]);
    }

    /**
     * A stylesheet of the site for the frame - in the head after the store css
     * @param string $href
     * @return void
     */
    public static function Stylesheet(string $href) : void
    {
        StoreFrame::$stylesheets[] = $href;
    }

    public static function Stylesheets() : array
    {
        return StoreFrame::$stylesheets;
    }

    /**
     * The first breadcrumb item of the products (default: 'Products' - the products list)
     * @param string $title
     * @param string $href
     * @return void
     */
    public static function HomeAction(string $title, string $href) : void
    {
        StoreFrame::$homeAction = array($title, $href);
    }

    public static function GetHomeAction() : ?array
    {
        return StoreFrame::$homeAction;
    }
}

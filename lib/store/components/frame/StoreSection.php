<?php
include_once("components/PageSection.php");
include_once("components/Container.php");
include_once("storeconfig.php");

/**
 * A section of the store frame (header, menu, page footer, footer, cookies): a page section whose contents are
 * built when the frame initializes it - the constructor only takes the options, so a site sets them up in its site
 * init (StoreFrame) at no cost for the requests without a frame.
 */
abstract class StoreSection extends PageSection
{
    /**
     * The stylesheet of the section (storefront/css/frame/<Section>.css) - the frame adds it
     * @var string
     */
    protected string $stylesheet = "";

    /**
     * The stylesheets of this section for the head - a site section adds its own to the store one
     * @return array
     */
    public function stylesheets() : array
    {
        return ($this->stylesheet !== "") ? array($this->stylesheet) : array();
    }

    /**
     * Build the contents of the section into '$content' (the content container of a section)
     * @param Container $content
     * @param StorePage $page
     * @return void
     */
    abstract public function fill(Container $content, StorePage $page) : void;

    /**
     * Build the contents of this section for the frame '$page'
     * @param StorePage $page
     * @return void
     */
    public function initialize(StorePage $page) : void
    {
        $this->fill($this->content, $page);
    }
}

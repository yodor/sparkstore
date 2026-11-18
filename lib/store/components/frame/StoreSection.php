<?php
include_once("components/PageSection.php");
include_once("components/Container.php");

/**
 * A section of the store frame (header, menu, page footer, footer, cookies): a page section whose contents are
 * built when the frame initializes it - the constructor only takes the options, so a site sets them up in its site
 * init (StoreFrame) at no cost for the requests without a frame.
 */
abstract class StoreSection extends PageSection
{
    /**
     * Build the contents of the section into '$content' (the content container of a section)
     * @param Container $content
     * @param StorePageBase $page
     * @return void
     */
    abstract public function fill(Container $content, StorePageBase $page) : void;

    /**
     * Build the contents of this section for the frame '$page'
     * @param StorePageBase $page
     * @return void
     */
    public function initialize(StorePageBase $page) : void
    {
        $this->fill($this->content, $page);
    }
}

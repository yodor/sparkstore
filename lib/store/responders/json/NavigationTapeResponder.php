<?php
include_once("responders/json/JSONResponder.php");

/**
 * Product tapes of NavigationList items rendered after the page - for the items whose tape was left as a
 * placeholder (NavigationList::$eagerItems). The page creates its navigation lists as usual; this responder
 * asks the list for the tapes of the requested items.
 *   ?responder=NavigationTapeResponder&function=render&list=<list index>&items=<value>,<value>...
 * The tapes are the same for every visitor (random products), so browsers and the CDN may keep the answer.
 */
class NavigationTapeResponder extends JSONResponder
{
    /**
     * Seconds the answer may be kept by browsers and the CDN
     */
    const int MAX_AGE = 300;

    /**
     * Navigation lists of the page in creation order - the order is the same for every request of the page
     * @var array<int, NavigationList>
     */
    protected array $lists = array();

    /**
     * @param NavigationList $list
     * @return int index of the list - its 'list' request value
     */
    public function addList(NavigationList $list) : int
    {
        $this->lists[] = $list;
        return count($this->lists) - 1;
    }

    public function _render(JSONResponse $resp) : void
    {
        $index = (int)($_GET["list"] ?? -1);
        if (!isset($this->lists[$index])) throw new Exception("Unknown navigation list");

        $values = array_filter(explode(",", (string)($_GET["items"] ?? "")), fn($value) => $value !== "");
        if (count($values) < 1) throw new Exception("No items requested");

        //only values of existing items are rendered - see NavigationList::renderTapes
        $this->lists[$index]->renderTapes(array_slice($values, 0, 50));
        $resp->setMaxAge(NavigationTapeResponder::MAX_AGE);
    }
}

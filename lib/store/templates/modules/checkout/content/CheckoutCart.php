<?php
include_once("store/objects/data/GTMItems.php");
include_once("objects/data/GTMQueue.php");
include_once("store/templates/modules/checkout/content/CheckoutContent.php");
include_once("store/utils/cart/Cart.php");
include_once("store/utils/cart/CartEntry.php");
include_once("store/utils/SellableItem.php");
include_once("store/utils/url/ProductListURL.php");
include_once("input/renderers/Input.php");

/**
 * The cart. Changes come only as POST (cart=add|increment|decrement|remove|clear, item=<hash>; add: prodID and
 * variant - base64 JSON {name: value}), then the cart page again (GET). The prices are the ones of the items
 * in the cart (session) - reviewed in 6.x.
 */
class CheckoutCart extends CheckoutContent
{
    const array CHANGES = array("add", "increment", "decrement", "remove", "clear");

    public function initialize() : void
    {
        $cart = Cart::Instance();
        $page = $this->page();

        $page->getCartComponent()->setModifyEnabled(true);
        //the module folder of this request (default /checkout/cart)
        $page->getCartComponent()->setPostURL(Module::PathURL("cart"));
        $page->getCartComponent()->initialize();

        Session::SetIfActive("checkout.navigation.back", Module::PathURL("cart")->toString());

        if ($cart->itemsCount() > 0) {
            //clear: a POST form, sent by the left button
            $clear = new Container(false);
            $clear->setTagName("form");
            $clear->setComponentClass("");
            $clear->setName("CartClear");
            $clear->setAttribute("name", "CartClear");
            $clear->setAttribute("method", "post");
            $clear->setAttribute("action", Module::PathURL("cart")->toString());
            $clear->items()->append(new Input("hidden", "cart", "clear"));
            $page->base()->items()->append($clear);

            $this->navigation(CheckoutContent::NAV_LEFT, tr("Изпразни кошницата"), "empty", "javascript:document.forms.CartClear.submit();");
        }

        $href = (string)Session::Get("shopping.list", (new ProductListURL())->toString());
        $this->navigation(CheckoutContent::NAV_CENTER, tr("Продължи пазаруването"), "continue_shopping", $href);

        if ($cart->itemsCount() > 0) {
            //the cashier: confirm opens what is missing (login, delivery ...)
            $this->navigation(CheckoutContent::NAV_RIGHT, tr("Каса"), "checkout", Module::PathURL("confirm")->toString());
        }
    }

    public function processInput() : void
    {
        parent::processInput();

        if (strcmp((string)($_SERVER["REQUEST_METHOD"] ?? "GET"), "POST") !== 0) return;
        $change = (string)($_POST["cart"] ?? "");
        if (!in_array($change, CheckoutCart::CHANGES, true)) return;

        $cart = Cart::Instance();
        $hash = (string)($_POST["item"] ?? "");

        try {
            switch ($change) {
                case "add":
                    $this->add($cart);
                    //the first product of a visitor (no list remembered without a session): 'continue shopping'
                    //goes back to the page it was added from
                    if (!Session::Contains("shopping.list")) {
                        $referer = (string)($_SERVER["HTTP_REFERER"] ?? "");
                        $host = (string)parse_url("//" . ($_SERVER["HTTP_HOST"] ?? ""), PHP_URL_HOST);
                        if ($referer !== "" && $host !== "" && strcasecmp((string)parse_url($referer, PHP_URL_HOST), $host) === 0) {
                            $query = (string)parse_url($referer, PHP_URL_QUERY);
                            Session::Set("shopping.list", (string)parse_url($referer, PHP_URL_PATH) . (($query !== "") ? "?" . $query : ""));
                        }
                    }
                    break;
                case "increment":
                    $cart->increment($hash);
                    break;
                case "decrement":
                    $cart->decrement($hash);
                    break;
                case "remove":
                    $cart->remove($hash);
                    break;
                case "clear":
                    $cart->clear();
                    break;
            }
            $cart->store();
        }
        catch (Exception $e) {
            Debug::ErrorLog("Cart change '$change' failed: " . $e->getMessage());
            Session::SetAlert(tr("Грешка") . ": " . $e->getMessage());
        }
        //the cart page again - no change on reload
        $this->redirect("cart");
    }

    /**
     * Add the product prodID with the selected variants (base64 JSON object name => value)
     * @param Cart $cart
     * @return void
     */
    protected function add(Cart $cart) : void
    {
        try {
            $prodID = (int)($_POST["prodID"] ?? -1);
            if ($prodID < 1) throw new Exception("Product does not exist or is not accessible right now");

            $sellable = SellableItem::Load($prodID);
            if ($sellable->getStockAmount() < 1) throw new Exception("SellableProducts returned stock amount low");

            $encoded = (string)($_POST["variant"] ?? "");
            if ($encoded !== "") {
                $variant = json_decode((string)Spark::Base64URLDecode($encoded), true);
                if (!is_array($variant)) throw new Exception("Incorrect variant data");
                foreach ($variant as $name => $value) {
                    if (!is_string($name) || !is_scalar($value)) throw new Exception("Incorrect variant data");
                    if (!$sellable->haveVariant($name)) throw new Exception("Unknown option '$name'");
                    $itemVariant = $sellable->getVariant($name);
                    if (!$itemVariant->haveParameter((string)$value)) throw new Exception("Unknown value of '$name'");
                    $itemVariant->setSelected((string)$value);
                }
            }

            $cart->addItem(new CartEntry($sellable));
            //GA4 add_to_cart - on the cart page after the redirect
            GTMQueue::Queue(GTMItems::Event("add_to_cart", array(GTMItems::Item($sellable)), $sellable->getPriceInfo()->getSellPrice()));
        }
        catch (Exception $e) {
            Debug::ErrorLog("Cart add failed: " . $e->getMessage());
            Session::SetAlert(tr("Този продукт е недостъпен.") . " " . tr("Грешка") . ": " . $e->getMessage());
            header("Location: " . (new ProductListURL())->toString());
            exit;
        }
    }

    protected function getContentTitle() : string
    {
        return tr("Съдържание на кошницата");
    }
}

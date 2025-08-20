<?php
include_once("session.php");
include_once("class/pages/CheckoutPage.php");
include_once("store/utils/cart/Cart.php");
include_once("store/beans/SellableProducts.php");

$page = new CheckoutPage();
$page->modify_enabled = TRUE;

$cart = Cart::Instance();


$sellable = null;
$itemHash = "";
$redirect = FALSE;
try {

    if (isset($_GET["item"])) {
        $itemHash = $_GET["item"];
    }

    if (isset($_GET["add"])) {

        $prodID = -1;

        if (isset($_GET["prodID"])) {
            $prodID = (int)$_GET["prodID"];
        }

        try {

            if ($prodID < 1) throw new Exception("Product does not exist or is not accessible right now");

            $sellable = SellableItem::Load($prodID);

            if ($sellable->getStockAmount() < 1) throw new Exception("SellableProducts returned stock amount low");

            $variant = null;
            if (isset($_GET["variant"])) {
                $variant = json_decode(base64_url_decode($_GET["variant"]));
                foreach ($variant as $name => $value) {
                    if ($sellable->haveVariant($name)) {
                        $itemVariant = $sellable->getVariant($name);
                        if ($itemVariant->haveParameter($value)) {
                            $itemVariant->setSelected($value);
                        }
                    }
                }
            }

            $item = new CartEntry($sellable);
            $cart->addItem($item);

            $redirect = TRUE;


        } catch (Exception $e) {

            Session::set("alert", "Този продукт е недостъпен. Грешка: " . $e->getMessage());
            header("Location: list.php");
            exit;
        }
    }

    //client increase product item amount from the + button
    else if (isset($_GET["increment"])) {
        $cart->increment($itemHash);
        $redirect = TRUE;
    }
    //client decrease product item amount from the - button
    else if (isset($_GET["decrement"])) {
        $cart->decrement($itemHash);
        $redirect = TRUE;
    }
    //client remove product item from the x button
    else if (isset($_GET["remove"])) {
        $cart->remove($itemHash);
        $redirect = TRUE;
    }
    else if (isset($_GET["clear"])) {
        $cart->clear();
        $redirect = true;
    }

    //redirect to store cart in session and clean the url
    if ($redirect) {
        $cart->store();
        header("Location: cart.php");
        exit;
    }
}
catch (Exception $e) {
    Session::SetAlert("Грешка: " . $e->getMessage());
    header("Location: cart.php");
    exit;
}

$page->setTitle(tr("Съдържание на кошницата"));


$page->startRender();


/**
 * 1. cart.php
 * 2. customer.php
 * 3. chooser courier
 * 4. choose destination address option (personal, office)
 * 5. confirm
 */

echo "<h1 class='Caption'>" . $page->getTitle() . "</h1>";

$page->drawCartItems();

if ($page->total) {
    $action = $page->getAction(CheckoutPage::NAV_LEFT);
    $action->setTitle(tr("Изпразни кошницата"));
    $action->setClassName("empty");
    $action->getURL()->fromString("cart.php?clear");
}

$action = $page->getAction(CheckoutPage::NAV_CENTER);
$action->setTitle(tr("Продължи пазаруването"));
$action->setClassName("continue_shopping");
$href = Session::Get("shopping.list", new ProductListURL());
$action->getURL()->fromString($href);

if ($page->total) {
    $action = $page->getAction(CheckoutPage::NAV_RIGHT);
    $action->setTitle(tr("Каса"));
    $action->setClassName("checkout");
    $action->getURL()->fromString("customer.php");
}

$page->renderNavigation();

Session::set("checkout.navigation.back", URL::Current()->toString());

$page->finishRender();

?>

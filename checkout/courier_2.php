<?php
if (!isset($courier)) exit;
if (!isset($page)) exit;

if (!$page instanceof CheckoutPage) exit;
if (!$courier instanceof DeliveryCourier) exit;

include_once("store/forms/CourierOfficeInputForm.php");
include_once("store/beans/CourierAddressesBean.php");

class EkontOfficeFormProcessor extends FormProcessor
{

    public function processImpl(InputForm $form) : void
    {
        parent::processImpl($form);

        if ($this->getStatus() != IFormProcessor::STATUS_OK) return;

        $page = StorePage::Instance();

        $dbt = new BeanTransactor($this->bean, $this->editID);
        $dbt->appendValue("userID", $page->getUserID());

        $dbt->processForm($form);

        //will do insert or update
        $dbt->processBean();


        Cart::Instance()->getDelivery()->getSelectedCourier()->setSelectedOption(DeliveryOption::COURIER_OFFICE);

        header("Location: confirm.php");
        exit;
    }
}

$bean = new CourierAddressesBean();
$proc = new EkontOfficeFormProcessor();
$proc->setBean($bean);

$form = new CourierOfficeInputForm();
$form->setName("EkontOffice");

$empty = "";
$eorow = $bean->getResult("userID", $page->getUserID());
if (!$eorow) {
    $empty = "empty";
}
else {
    $editID = (int)$eorow[$bean->key()];
    $proc->setEditID($editID);
    $form->loadBeanData($editID, $bean);
}

$frend = new FormRenderer($form);

$proc->process($form);

$page->setTitle(tr("Избор на Спиди офис"));

$page->startRender();


$page->drawCartItems();

echo "<div class='item ekont_office $empty'>";

    echo "<div class='Caption'>" . tr("Избран офис на Спиди") . "</div>";

    echo "<div class='selected_office'>";
    echo str_replace("\r", "<br>", (string)$form->getInput("office")->getValue());
    echo "</div>";

    $frend->getSubmitLine()->setRenderEnabled(false);
    $frend->render();

    echo "<a class='ColorButton' href='javascript:changeEkontOffice();'>" . tr("Изберете друг офис") . "</a>";

echo "</div>";//ekont_office

echo "<div class='item ekont_locator'>";

    echo "<div class='Caption'>";
    echo tr("Изберете офис на Еконт за доставка");
    echo "</div>";
?>

    <iframe async id="ekont_frame" height=450 width="100%" border=0 frameborder="0" allowtransparency="true" src="https://www.bgmaps.com/templates/econt?office_type=all&shop_url=<?php
    echo SITE_URL; ?>"></iframe>

<?php
echo "</div>"; //ekont_locator

$back_url = Session::get("checkout.navigation.back", "delivery.php");

$action = $page->getAction(CheckoutPage::NAV_LEFT);
$action->setTitle(tr("Назад"));
$action->setClassName("edit");
$action->getURL()->fromString($back_url);

$action = $page->getAction(CheckoutPage::NAV_RIGHT);
$action->setTitle(tr("Продължи"));
$action->setClassName("checkout");
$action->getURL()->fromString("javascript:document.forms.EkontOffice.submit()");

$page->renderNavigation();


?>
<script type='text/javascript'>

    window.addEventListener("message", receiveMessage, false);

    function receiveMessage(event) {

        if ((event.origin === "http://www.bgmaps.com") || (event.origin === "https://www.bgmaps.com")) {

        } else {

            console.log("event.origin missmatch");
            console.log(event.origin);

            return;
        }

        // ...
        console.log(event.origin);
        console.log(event.data);

        var office = event.data.split("||");
        var text = office[0] + " " + office[1] + "\r\n";
        text += office[3] + "\r\n";
        text += office[2] + "\r\n";

        $('.item.ekont_office .selected_office').html(text.replace("\r\n", "<BR>"));

        $('.item.ekont_office .TextArea TEXTAREA[name="office"]').html(text);

        showAlert("Избрахте офис на 'Еконт'<br>" + text);

        $(".item.ekont_office").removeClass("empty");
        $(".item.ekont_locator").css("display", "none");

//     $(".selected_office").css("display", "block");
    }

    function changeEkontOffice() {
        $(".item.ekont_office").addClass("empty");
        //$(".selected_office").css("display", "none");
        $(".item.ekont_locator").css("display", "block");
    }

    onPageLoad(function () {
        $(".item.ekont_locator").css("display", "none");

    });
</script>


<?php
$page->finishRender();
?>

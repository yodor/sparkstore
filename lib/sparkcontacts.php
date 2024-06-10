<?php
include_once("class/pages/StorePage.php");

include_once("store/beans/ContactAddressesBean.php");
include_once("store/responders/json/ContactRequestFormResponder.php");

$page = new StorePage();

$contacts_handler = new ContactRequestFormResponder();

$page->setTitle(tr("Контакти"));
//$page->startRender();
//$page->setTitle("Контакти");
//

$cfg = new ConfigBean();
$cfg->setSection("store_config");

$page->startRender();


echo "<h1 class='Caption'>" . tr($page->getTitle()) . "</h1>";

echo "<div class='columns'>";


$cabean = new ContactAddressesBean();
$qry = $cabean->queryFull();
$qry->select->order_by = " position ASC ";
$num_addresses = $qry->exec();

$maps_src = "";
if ($num_addresses<1) {
    $maps_src = $cfg->get("maps_url");
}
echo "<div class='column map'>";

echo "<a name='map'></a>";
echo "<div class='panel map'>";
echo "<iframe id=google_map src='$maps_src'  frameborder='0' allowfullscreen='' aria-hidden='false' tabindex='0'></iframe>";
echo "</div>";

echo "</div>"; //column

echo "<div class='column addresses'>";

$cabean = new ContactAddressesBean();
$qry = $cabean->queryFull();
$qry->select->order_by = " position ASC ";
$num_addresses = $qry->exec();
while ($carow = $qry->next()) {

    echo "<div class='details' pos='{$carow["position"]}' onClick='updateMap(this);' map-url='{$carow["map_url"]}'>";

    echo "<div class='item city' >";
    echo $carow["city"];
    echo "</div>";

    echo "<div class='item address'>";
    echo $carow["address"];
    echo "</div>";

    echo "<div class='item email'>";
    $email = strip_tags($carow["email"]);
    if (strlen($email) > 0) {
        echo "Email: <a href='mailto:$email'>$email</a>";
    }
    echo "</div>";

    echo "<div class='item phone'>";
    $phone = strip_tags($carow["phone"]);
    if (strlen($phone) > 0) {
        echo "Телефон: <a href='tel:$phone'>$phone</a>";
    }
    echo "</div>";

    echo "</div>";//details

}

echo "</div>"; //column

echo "</div>";//columns


echo "<a class='ColorButton ContactsButton' onClick='showContactsForm()'>Изпрати запитване</a>";


?>

<script type="text/javascript">
    function showContactsForm()
    {
        let contacts_dialog = new JSONFormDialog();
        contacts_dialog.setResponder("ContactRequestFormResponder");
        contacts_dialog.caption="Изпрати запитване";
        contacts_dialog.show();
    }
    function updateMap(elm)
    {
        $("#google_map").attr("src", $(elm).attr("map-url"));
    }
    onPageLoad(function(){
        let elm = $(".details[pos='1']");
        updateMap(elm);
    });
</script>

<?php
$page->finishRender();
?>

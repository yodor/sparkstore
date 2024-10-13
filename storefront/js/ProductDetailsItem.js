function formatPrice(n) {
    return n.toFixed(2);
}

/**
 *
 * @returns {{}}
 */
function getSelectedVariants()
{
    let result = {};

    //check variants selected
    let variant_groups = document.querySelectorAll(".group.variants");
    variant_groups.forEach((group)=>{
        group.querySelectorAll(".item.variant").forEach((variant)=>{
            let option_name = variant.getAttribute("name");
            let selected_parameter = variant.querySelector(".parameter[selected]");

            if (!selected_parameter) {
                throw new Error("Изберете опция за " + option_name);
            }

            result[option_name] = selected_parameter.getAttribute("value");
        })
    });

    return result;
}
/**
 *
 * @param elm {HTMLElement}
 */
function selectVariantParameter(elm) {
    let list = elm.closest(".list");
    list.querySelectorAll(".parameter").forEach((item) => {
        item.removeAttribute("selected");
    });

    let variant = list.closest(".item.variant");
    variant.querySelector(".value").innerHTML = elm.getAttribute("value");

    elm.setAttribute("selected", "");
}

function addToCart() {

    //TODO check element existance and use value then
    //let stock_amount = parseInt(document.querySelector(".stock_amount .value").innerText);

    // if (stock_amount < 1) {
    //     showAlert("В момента няма наличност от този артикул");
    //     return;
    // }

    let selected = {};

    //check variants selected

    try {

       selected = this.getSelectedVariants()

    } catch (e) {
        showAlert(e.message);
        return;
    }


    // Usage
    let encoded = bytesToBase64(new TextEncoder().encode(JSON.stringify(selected)));


    let current_url = new URL(window.location.href);
    let prodID = document.querySelector(".ProductDetailsItem")?.getAttribute("productID");

    let url = new URL(LOCAL + "/checkout/cart.php", location.href);
    url.searchParams.set("add", "");
    url.searchParams.set("prodID", prodID);
    url.searchParams.set("variant", encoded);

    console.log(url.href);
    window.location.href = url.href;

}

function base64ToBytes(base64) {
    const binString = atob(base64);
    return Uint8Array.from(binString, (m) => m.codePointAt(0));
}

function bytesToBase64(bytes) {
    const binString = Array.from(bytes, (x) => String.fromCodePoint(x)).join("");
    return btoa(binString);
}


function showNotifyInstockForm() {
    let notify_dialog = new JSONFormDialog();
    notify_dialog.setTitle("Уведоми ме при наличност");
    notify_dialog.setResponder("NotifyInstockFormResponder");
    notify_dialog.show();
}

function showProductQueryForm() {
    let query_dialog = new JSONFormDialog();
    query_dialog.setTitle("Запитване");
    query_dialog.setResponder("QueryProductFormResponder");
    query_dialog.show();
}

function showOrderProductForm() {

    let selected_variants = {};

    try {
        selected_variants = this.getSelectedVariants();

    } catch (e) {
        showAlert(e.message);
        return;
    }

    let order_dialog = new JSONFormDialog();
    order_dialog.setTitle("Бърза поръчка");
    order_dialog.setResponder("OrderProductFormResponder");

    const keys = Object.keys(selected_variants);
    keys.forEach((key) => {
        order_dialog.getJSONRequest().addPostParameter("variant[]", key + ": " + selected_variants[key]);
    });

    order_dialog.show();
}

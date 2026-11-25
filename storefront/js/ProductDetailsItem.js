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
                throw new Error(tr("Select an option for %s", option_name));
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

function addToCart(conversionID) {



    //TODO check element existance and use value then
    //let stock_amount = parseInt(document.querySelector(".stock_amount .value").innerText);

    // if (stock_amount < 1) {
    //     showAlert("This item is not in stock at the moment");
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

    if (conversionID) {
        document.sparkGTM.emitConversion(conversionID);
    }

    //the cart of the checkout module takes changes only by POST
    const form = document.createElement("form");
    form.method = "post";
    form.action = document.Spark.LOCAL + "/checkout/cart";
    for (const [name, value] of Object.entries({cart: "add", prodID: prodID, variant: encoded})) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();

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
    notify_dialog.setTitle(tr("Notify me when in stock"));
    notify_dialog.setResponder("NotifyInstockFormResponder");
    notify_dialog.show();
}

function showProductQueryForm() {
    let query_dialog = new JSONFormDialog();
    query_dialog.setTitle(tr("Query"));
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
    order_dialog.setTitle(tr("Fast order"));
    order_dialog.setResponder("OrderProductFormResponder");

    const keys = Object.keys(selected_variants);
    keys.forEach((key) => {
        order_dialog.getJSONRequest().addPostParameter("variant[]", key + ": " + selected_variants[key]);
    });

    order_dialog.show();
}

/**
 *
 * @param phone {string}
 * @param conversionID {string} AW-CONVERSION_ID/CONVERSION_LABEL
 */
function initiateCall(phone, conversionID) {

    //GA4: a call is a lead
    gtag('event', 'generate_lead', {lead_type: 'phone'});

    if (conversionID) {
        document.sparkGTM.emitConversion(conversionID);
    }

    // Remove any non-digit characters except + (E.164 format is safest)
    const cleaned = phone.replace(/[^\d+]/g, '');

    // Option A: Most compatible
    window.location.href = `tel:${cleaned}`;


    // gtag("event", "conversion", {
    //     send_to: "AW-XXX/LABEL",
    //     value: 50,
    //     currency: "EUR",
    //     user_data: { email: "user@example.com" },
    // });
}
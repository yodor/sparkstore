
function formatPrice(n)
{
    return n.toFixed(2);
}

let curr_pos=0;

onPageLoad(function() {
    //return assigned SparkObject in data
    let image_popup = $(".image_preview .ImagePopup").data("ImagePopup");
    image_popup.addObserver(popupEvent);

    let image = $(".image_preview .ImagePopup");
    image.on("SwipeAction", function(e) {

        if (e.message == "right") {
            next();
        }
        else if (e.message == "left") {
            prev();
        }

    });

    const listener = new SwipeListener(image);
});

function popupEvent(spark_event) {

    if (spark_event.isEvent(ImagePopup.EVENT_CLOSED)) {
        updateImage();
    }
    else if (spark_event.isEvent(ImagePopup.EVENT_POSITION_CHANGED)) {
        curr_pos = spark_event.source.pos;
    }
}
function updateImage()
{

    //deselect all
    $(".image_gallery .item").removeAttr("active");

    let current_item = $(".image_gallery .item[pos='"+curr_pos+"']");
    current_item.attr("active", 1);
    let itemID = current_item.attr("itemID");

    let main_image = $(".image_preview IMG");

    let src = main_image.attr("src");

    let target_url = new URL(src, document.URL);

    target_url.searchParams.set("id", itemID);

    main_image.attr("src", target_url);

    $(".image_preview .ImagePopup").attr("itemID", itemID);

}

function galleryItemClicked(elm)
{
    curr_pos = $(elm).attr("pos");
    updateImage();
}


function next()
{
    let max_pos = $(".image_preview").attr("max_pos")-1;

    curr_pos++;
    if (curr_pos>max_pos) {
        curr_pos = 0;
    }

    updateImage();

}

function prev()
{
    let max_pos = $(".image_preview").attr("max_pos")-1;

    curr_pos--;
    if (curr_pos < 0) {
        curr_pos = max_pos;
    }

    updateImage();
}


function selectVariantParameter(elm)
{
    let list = $(elm).parents(".list").first();

    let variant = list.parents(".item.variant").first();

    list.children(".parameter").each(function (){
        $(this).removeAttr("selected");
    });

    $(elm).attr("selected", "");
    let parameter = $(elm).attr("value");
    variant.children(".value").first().html(parameter);

}

function addToCart() {

    let stock_amount = parseInt($(".stock_amount .value").html());
    var selected = {};

    //check variants selected
    let variants = $(".group.variants");

    try {
        let items = variants.find(".item.variant");
        for (let a=0;a<items.length;a++) {

            let item = items[a];
            let option_name = $(item).attr("name");
            let option_value = $(item).find(".parameter[selected]");
            if (!option_value.length) {
                throw "Изберете опция за " + option_name
            }

            let value = option_value.attr("value");
            console.log(option_name + " => " + value);
            selected[option_name] = value;
        }

    }
    catch (e) {
        showAlert(e);
        return;
    }



    // Usage
    let encoded = bytesToBase64(new TextEncoder().encode(JSON.stringify(selected)));
    //new TextDecoder().decode(base64ToBytes("YSDEgCDwkICAIOaWhyDwn6aE")); // "a Ā 𐀀 文 🦄"


    // if (stock_amount < 1) {
    //     showAlert("В момента няма наличност от този артикул");
    //     return;
    // }

    let current_url = new URL(window.location.href);
    let prodID = $(".ProductDetailsItem").first().attr("productID");

    let url = new URL(LOCAL+"/checkout/cart.php", location.href);
    url.searchParams.set("add","");
    url.searchParams.set("prodID", prodID);
    url.searchParams.set("variant", encoded);

    console.log(url.href);
    window.location.href=url.href;

}

function base64ToBytes(base64) {
    const binString = atob(base64);
    return Uint8Array.from(binString, (m) => m.codePointAt(0));
}

function bytesToBase64(bytes) {
    const binString = Array.from(bytes, (x) => String.fromCodePoint(x)).join("");
    return btoa(binString);
}


function showNotifyInstockForm()
{
    let notify_dialog = new JSONFormDialog();
    notify_dialog.caption="Уведоми ме при наличност";
    notify_dialog.setResponder("NotifyInstockFormResponder");
    notify_dialog.show();
}
function showProductQueryForm()
{
    let query_dialog = new JSONFormDialog();
    query_dialog.caption="Запитване";
    query_dialog.setResponder("QueryProductFormResponder");
    query_dialog.show();
}
function showOrderProductForm()
{
    let order_dialog = new JSONFormDialog();
    order_dialog.caption="Бърза поръчка";
    order_dialog.setResponder("OrderProductFormResponder");

    //check variants selected
    let variants = $(".group.variants");

    try {
        variants.find(".item.variant").each(function () {
            let option_name = $(this).attr("name");
            let option_value = $(this).find(".parameter[selected]");

            if (!option_value.length) {
                throw "Изберете опция за " + option_name
            }

            let value = option_value.attr("value");
            //console.log(option_name + " => " + value);
            order_dialog.req.addPostParameter("variant[]", option_name+": "+value);

        });
    }
    catch (e) {
        showAlert(e);
        return;
    }

    order_dialog.show();
}

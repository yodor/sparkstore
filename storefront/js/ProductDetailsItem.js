
function formatPrice(n)
{
    return n.toFixed(2);
}

function updatePrice() {
    //return;
    //console.log("Update Price");

    let active_color = $(".color_chooser .value .color_button[active='1']");
    let active_size = $(".size_chooser .value .size_button[active='1']");

    let piID = active_size.attr("piID");
    let pclrID = active_color.attr("pclrID");


    let attrib = sellable.getAttributes(piID);

    $(".group.attributes .viewport").empty();

    let item_template = "<div class='item'><label></label><span class='value'></span></div>";

    let amount_info = $(".group.stock_amount .value");
    $(".group.stock_amount").addClass("disabled");

    let sell_price = $(".group.pricing .price_info .sell .value");
    let old_price = $(".group.pricing .price_info .old .value");
    $(".group.pricing .price_info .old").addClass("disabled");
    $(".images .image_preview").removeClass("promo");

    let priceInfo = null;
    if (piID>0) {

        if (typeof attrib == "object") {
            Object.entries(attrib).forEach(entry => {
                //key = name
                //value = contents
                const [idx, attribute_value] = entry;

                let value = attribute_value.value;
                let name = attribute_value.name;

                let attribute_item = $(item_template);
                attribute_item.find("LABEL").first().text(name);
                attribute_item.find(".value").first().text(value);
                $(".group.attributes .viewport").append(attribute_item);

            });
        }

        priceInfo = sellable.getPriceInfo(piID);

        amount_info.text(priceInfo.stock_amount);
        $(".group.stock_amount").removeClass("disabled");


        sell_price.text(formatPrice(priceInfo.sell_price));

        old_price.text(formatPrice(priceInfo.old_price));

        if (sellable.isPromotion(piID)) {
            $(".group.pricing .price_info .old").removeClass("disabled");
            $(".images .image_preview").addClass("promo");
        }

    }
    //broad selection of price ie sellables with different pricing - show price range
    else {
        let price_min = 0.0;
        let price_max = 0.0;
        priceInfo = sellable.getPriceInfosByColorID(pclrID);
        Object.entries(priceInfo).forEach(entry => {
            const[piID, pinfo] = entry;
            if (price_min == 0) price_min = pinfo.sell_price;
            if (price_max == 0) price_max = pinfo.sell_price;
            if (price_min > pinfo.sell_price) price_min = pinfo.sell_price;
            if (pinfo.sell_price > price_max) price_max = pinfo.sell_price;

        });
        $(".group.price_cart .sell_price .old").attr("enabled", 0);
        let price_label = formatPrice(price_min);
        if (price_min != price_max) {
            price_label = formatPrice(price_min) + " - " + formatPrice(price_max);
        }
        sell_price.text(price_label)

    }

}


function changeColor(elm) {
    let color_button = $(".color_chooser .value .color_button");
    color_button.attr("active", "0");

    $(elm).attr("active", "1");

    let pclrID = $(elm).attr("pclrID");

    $(".current_color .value").text(sellable.getColorName(pclrID));

    renderSizeChooser(pclrID);
    renderImageGallery(pclrID);
    updatePrice();
}

function changeSizing(elm) {
    let size_buttons = $(".size_chooser .value .size_button");
    size_buttons.attr("active", "0");

    $(elm).attr("active", "1");

    $(".current_size .value").text($(elm).text());

    updatePrice();
}

//update the main image
function updateImagePreview(elm) {

    //deselect all gallery items
    $(".image_gallery .list .item").attr("active", "0");

    let itemClass = $(elm).attr("itemClass");
    let itemID = $(elm).attr("itemID");

    let imageURL = new URL(STORAGE_LOCAL, location.href);
    imageURL.searchParams.set("cmd", "image");
    imageURL.searchParams.set("class", itemClass);
    imageURL.searchParams.set("id", itemID);
    imageURL.searchParams.set("width", sellable.sellable.width);
    imageURL.searchParams.set("height", sellable.sellable.height);

    $(".images .image_preview IMG").attr("src", imageURL.href);

    $(elm).attr("active", "1");

    let imagePopup = $(".images .image_preview .ImagePopup");
    imagePopup.attr("itemClass", itemClass);
    imagePopup.attr("itemID", itemID);

}

//product photos gallery is matched at pclrID=0
function renderImageGallery(selected_pclrID)
{
    if (!sellable.haveGalleryItems(selected_pclrID)) {
        console.log("No image gallery for pclrID="+selected_pclrID);
        return;
    }

    console.log("Image Gallery for pclrID: " + selected_pclrID);

    let galleryItems = sellable.galleryItems(selected_pclrID);

    $(".images .image_gallery").empty();

    let list_template = "<div class='list'></div>";

    let item_template = "<div class='item ImagePopup' onClick='javascript:updateImagePreview(this)' relation='gallery'></div>";

    let list = $(list_template);
    list.attr("pclrID", selected_pclrID);

    if (galleryItems.length == 1) {
        list.addClass("single");
    }

    Object.entries(galleryItems).forEach(entry=>{
        const[key, item] = entry;

        let gallery_item = $(item_template);

        gallery_item.attr("itemClass", item.className);
        gallery_item.attr("itemID", item.id);

        let imageURL = new URL(item.storageURL, location.href);
        imageURL.searchParams.set("class", item.className);
        imageURL.searchParams.set("id", item.id);
        imageURL.searchParams.set("cmd", "image");
        imageURL.searchParams.set("size", "128");

        gallery_item.attr("style", "background-image:url("+imageURL.href+")");
        list.append(gallery_item);

    });


    $(".images .image_gallery").append(list);

    updateImagePreview(list.find(".item").first());

}

function renderColorButtons(selected_pclrID)
{
    let color_chips = sellable.getColorChips();
    //console.log(color_chips);

    let color_button_template = "<span class='color_button' onClick='javascript:changeColor(this)'></span>";
    let image_template = "<img src=''>";
    let simple_color_template = "<span class='simple_color'></span>";

    $(".color_chooser .value").empty();

    Object.entries(color_chips).forEach(entry => {
        //key = pclrID
        //value = StorageItem data => id, className, field, storageURL
        const [pclrID, value] = entry;

        let color_button = $(color_button_template);

        let color_name = sellable.getColorName(pclrID);

        //single unnamed color - sellable without color series
        if (!color_name) {
            $(".group.colors").css("display", "none");
        }

        color_button.attr("pclrID", pclrID);
        color_button.attr("color_name", color_name);
        color_button.attr("title", color_name);
        color_button.attr("active", 0);

        if (value && typeof value == "object") {
            let imageURL = new URL(value.storageURL, location.href);
            imageURL.searchParams.set("cmd", "image");
            imageURL.searchParams.set("class", value.className);
            imageURL.searchParams.set("id", value.id);
            imageURL.searchParams.set("size", 64);

            let image = $(image_template);
            image.attr("src", ""+imageURL.href);

            color_button.append(image);
        }
        else {
            let color_code = sellable.getColorCode(pclrID);
            let simple_color = $(simple_color_template);
            simple_color.css("background-color", color_code);
            color_button.append(simple_color);
        }

        $(".color_chooser .value").append(color_button);

    });

    let active_color_button = $(".color_chooser .color_button[pclrID='"+selected_pclrID+"']");

    changeColor(active_color_button);

}

function renderSizeChooser(pclrID) {


    let size_chooser = $(".sizing .item.size_chooser .value");
    $(".current_size .value").text();

    size_chooser.empty();

    let size_button_template = "<span class='size_button' onClick='javascript:changeSizing(this)'></span>";
    let size_values = sellable.getSizeValuesByColorID(pclrID);

    //console.log("size_values: " + Object.keys(size_values).length);

    Object.entries(size_values).forEach(entry => {
        //key = piID
        //value = size_value string
        const [piID, value] = entry;

        let size_button = $(size_button_template);
        size_button.attr("piID", piID);
        size_button.attr("pclrID", pclrID);
        size_button.text(value);

        size_chooser.append(size_button);

        if (Object.keys(size_values).length==1 && !value) {
            //sellable without sizing
            changeSizing(size_button);
            $(".group.sizing").css("display", "none");
        }
    });

}


function renderActiveSellable()
{
    let landing_piID = sellable.piID;

    let pclrID = sellable.getColorID(landing_piID);

    //console.log("Landing pclrID: "+pclrID);

    renderColorButtons(pclrID);

}

function addToCart() {

    let stock_amount = parseInt($(".stock_amount .value").html());

    let available_colors = $(".color_chooser .value .color_button").length;

    var active_colorID = $(".color_chooser .value .color_button[active='1']").attr("pclrID");
    console.log("Selected colorID: " + active_colorID);

    if (!active_colorID) {
        showAlert("Моля изберете цвят");
        return;
    }

    let available_sizes = $(".size_chooser .value .size_button").length;

    if (available_sizes>0) {
        selected_piID = $(".size_chooser .value .size_button[active='1']").attr("piID");
    }

    if (selected_piID < 1 || isNaN(selected_piID) && available_sizes>0) {
        showAlert("Моля изберете размер");
        return;
    }

    // if (stock_amount < 1) {
    //     showAlert("В момента няма наличност от този артикул");
    //     return;
    // }

    let url = new URL(LOCAL+"/checkout/cart.php", location.href);
    url.searchParams.set("add","");
    url.searchParams.set("piID", selected_piID);

    window.location.href=url.href;

}

function showProductQueryForm()
{
    let form = document.forms.QueryProductForm;

    let isShown = $(form).attr("shown");

    //isShown = true
    if (typeof isShown !== 'undefined' && isShown !== false) {
        //hide the form
        resetProductQueryForm();
    }
    else {
        $(form).attr("shown", 1);
    }

}
function resetProductQueryForm()
{
    let form = document.forms.QueryProductForm;
    $(form).removeAttr("shown");
    $(form).removeAttr("working");
    $(form).trigger("reset");
}
function sendProductQuery()
{
    let form = document.forms.QueryProductForm;


    let name = form.elements['fullname'].value;

    if (!name) {
        showAlert("Моля въведете Вашето име");
        return;
    }

    let email = form.elements['email'].value;
    if (!email) {
        showAlert("Моля въведете Вашият email адрес");
        return;
    }

    let query = form.elements['query'].value;
    if (!query) {
        showAlert("Моля въведете запитване");
        return;
    }

    let emailError = validateEmailText(email);
    if (emailError) {
        showAlert("Моля въведете валиден email адрес");
        return;
    }

    $(form).attr("working", 1);

    let req = new JSONRequest();
    req.setResponder("query_product");
    req.setFunction("sendQuery");
    req.setPostParameter("itemID", sellable.piID);
    req.setPostParameter("name", name);
    req.setPostParameter("email", email);
    req.setPostParameter("query", query);
    req.onSuccess = function(result){

        showAlert(result.json_result.contents);
        resetProductQueryForm();

    };

    req.start();
}
function applyFilter(elm) {

    let url = new URL(window.location);
    let params = url.searchParams;
    if (elm.value) {
        params.set(elm.name, elm.value);
    }
    else {
        params.delete(elm.name);
    }
    window.location = url;
    //console.log(url.toString());
    //console.log(elm.name);
}

function clearFilters() {

    let url = new URL(window.location);

    const values = document.querySelectorAll(".ActiveFilters [data-filter]");//document.forms["ProductListFilterInputForm"];
    for (let a = 0; a < values.length; a++) {
        const element = values.item(a);
        clearParameter(element, url);
    }

    redirectClean(url);
}

/**
 *
 * @param url {URL}
 */
function redirectClean(url)
{

    let delKeys = Array();

    url.searchParams.forEach(function(value, key, parent){
        if (value && key !== "SubmitForm") {
            //
        }
        else {
            delKeys.push(key);
        }
    });

    for (let a = 0; a < delKeys.length; a++) {
        url.searchParams.delete(delKeys[a]);
    }

    window.location = url;

}

/**
 *
 * @param element {HTMLElement}
 * @param url {URL}
 */
function clearParameter(element, url) {

    const clearParams = element.getAttribute("data-filter").split(";");
    for (let b = 0; b < clearParams.length; b++) {
        const name = clearParams[b];
        if (url.searchParams.has(name)) {
            url.searchParams.delete(name);
        }
    }

}

/**
 *
 * @param element {HTMLElement}
 */
function clearFilter(element) {
    let url = new URL(window.location);
    clearParameter(element, url);
    redirectClean(url);
}

/**
 *
 * @param elm {HTMLElement}
 */
function togglePanel(elm)
{

    let viewport = elm.closest(".panel").querySelector(".viewport");
    let isHidden = viewport.style.display;
    if (isHidden !== "block") {
        viewport.style.display = "block";
    }
    else {
        viewport.style.display = "none";
    }
}

function sendViewItemListEvent() {
    const listItems = document.querySelectorAll('[itemprop="itemListElement"]');
    const itemsForGA4 = [];

    listItems.forEach((li) => {
        // 1. Extract values from meta tags inside the <li> and <article>
        const sku = li.querySelector('meta[itemprop="sku"]')?.content;
        const name = li.querySelector('[itemprop="name"]')?.innerText;
        const category = li.querySelector('meta[itemprop="category"]')?.content;
        const url = li.querySelector('meta[itemprop="url"]')?.content;
        const position = li.querySelector('meta[itemprop="position"]')?.content;

        // 2. Target the BGN price specifically (ignores the EUR label)
        const priceEl = li.querySelector('.PriceLabel[name="EUR"] [itemprop="price"]');
        const price = priceEl ? parseFloat(priceEl.content || priceEl.innerText) : 0;


        if (sku) {
            itemsForGA4.push({
                item_id: sku,
                item_name: name,
                item_category: category,
                price: price,
                index: parseInt(position) || 0,
                item_url: url
            });
        }
    });

    if (itemsForGA4.length > 0) {
        gtag('event', 'view_item_list', {
            item_list_name: document.title, //document.querySelector('h1')?.innerText || 'Product Catalog',
            items: itemsForGA4
        });
    }
}
onPageLoad(function () {
    sendViewItemListEvent();
});
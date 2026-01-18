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

    const values = document.querySelectorAll(".ActiveFilters .value");//document.forms["ProductListFilterInputForm"];
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

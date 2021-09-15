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

    let params = url.searchParams;

    let form = document.forms["ProductListFilterInputForm"];
    let elements = form.elements;
    for (let a = 0; a < elements.length; a++) {

        let element = form.elements[a];
        let name = element.name;

        if (params.has(name)) {
            params.delete(name);
        }

    }

    window.location = url;
}

function togglePanel(elm)
{
    let e = $(elm).parents(".panel").first().children(".viewport").first();
    let is_hidden = e.css("display");
    if (is_hidden == "none") {
        e.css("display", "inline-block");
    }
    else {
        e.css("display", "none");
    }
}

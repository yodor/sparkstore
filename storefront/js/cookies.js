let storeCookies = new SparkCookies();

function acceptCookies()
{
    storeCookies.accept();
    updateCookies();
}

function updateCookies()
{
    let isAccepted = storeCookies.isAccepted();

    $(".section.cookies").attr("checked", 1);

    if (isAccepted) {
        $(".section.cookies").attr("accepted", 1);
    }
    else {
        $(".section.cookies").attr("accepted", 0);
    }
}

onPageLoad(function(){

    updateCookies();


});
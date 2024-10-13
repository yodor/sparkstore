

function acceptCookies()
{
    document.sparkCookies.accept();
    updateCookies();
}

function updateCookies()
{
    document.querySelector(".section.cookies").setAttribute("checked", document.sparkCookies.isAccepted());
}

document.sparkCookies = new SparkCookies();

onPageLoad(function(){
    updateCookies();
});
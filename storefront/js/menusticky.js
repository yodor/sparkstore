//to check when element gets position sticky
var observer = new IntersectionObserver(function(entries) {

    if (entries[0].intersectionRatio === 0) {
        document.querySelector(".section.menu").classList.add("sticky");

    }
    else if (entries[0].intersectionRatio === 1) {
        document.querySelector(".section.menu").classList.remove("sticky");
    }

}, {
    threshold: [0, 1]
});

onPageLoad(function(){
    observer.observe(document.querySelector(".section.header"));
});

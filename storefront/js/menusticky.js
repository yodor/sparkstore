//to check when element gets position sticky - the menu section of the store frame (none when a site disables it)
onPageLoad(function(){
    const header = document.querySelector(".section.header");
    const menu = document.querySelector(".section.menu");
    if (!header || !menu) return;
    const observer = new IntersectionObserver(function(entries) {
        if (entries[0].intersectionRatio === 0) {
            menu.classList.add("sticky");
        }
        else if (entries[0].intersectionRatio === 1) {
            menu.classList.remove("sticky");
        }
    }, {
        threshold: [0, 1]
    });
    observer.observe(header);
});

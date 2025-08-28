function fadeBanners()
{

    document.querySelectorAll(".banners").forEach((section)=>{
        let first = section.childNodes.item(0);
        if (!first)return;
        section.appendChild(section.removeChild(first));
    })

    setTimeout(()=>fadeBanners(),3000);

}

onPageLoad(function(){
    setTimeout(()=>fadeBanners(),3000);
});

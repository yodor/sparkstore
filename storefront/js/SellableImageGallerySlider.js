
onPageLoad(function() {
    document.imageGallery = new BannerSlider();
    document.imageGallery.setClass(".ProductDetailsItem");
    document.imageGallery.containerClass = ".SellableImageGallery";
    document.imageGallery.viewportClass = ".preview";
    document.imageGallery.autoplayEnabled = false;
    document.imageGallery.dotStyle = BannerSlider.STYLE_IMAGE;
    document.imageGallery.initialize();
    const observer = function(spark_event) {

        if (spark_event.isEvent(ImagePopup.EVENT_CLOSED)) {
            document.imageGallery.currentIndex = spark_event.source.pos;
            document.imageGallery.updateSlider();
        }

    };
    document.imagePopup.addObserver(observer);

});
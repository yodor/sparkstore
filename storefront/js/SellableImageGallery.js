class SellableImageGallery extends Component
{
    constructor()
    {
        super();
        this.pos = 0;
        this.setClass(".SellableImageGallery");
    }

    initialize() {
        super.initialize();


        this.preview = this.element.querySelector(".preview");
        this.gallery = this.element.querySelector(".gallery");

        this.gallery.itemsTotal = parseInt(this.preview.getAttribute("max_pos"));

        /**
         * @type {HTMLImageElement}
         */
        this.image = this.preview.querySelector("IMG");

        try {
            const listener = new SwipeListener(this.image);

            listener.onAction = (event) => {
                if (event.isEvent("right")) {
                    this.next();
                } else if (event.isEvent("left")) {
                    this.prev();
                }
            };
        }
        catch (ex) {
            //
        }

        const observer = this.popupEvent.bind(this);
        document.imagePopup.addObserver(observer);

    }


    /**
     *
     * @param spark_event {SparkEvent}
     */
    popupEvent(spark_event) {

        if (spark_event.isEvent(ImagePopup.EVENT_CLOSED)) {
            this.updateImage();
        } else if (spark_event.isEvent(ImagePopup.EVENT_POSITION_NEXT) || spark_event.isEvent(ImagePopup.EVENT_POSITION_PREV)) {
            this.pos = spark_event.source.pos;
        }
    }


    updateImage() {

        //deselect all
        this.gallery.querySelectorAll(".item").forEach((item)=>{
            item.removeAttribute("active");
        });

        const current_item = this.gallery.querySelector(".item[pos='" + this.pos + "']");
        current_item.setAttribute("active", 1);

        const itemID = current_item.getAttribute("itemID");
        this.preview.querySelector(".ImagePopup").setAttribute("itemID", itemID);

        const url = new URL(this.image.src, document.URL);
        url.searchParams.set("id", itemID);

        this.image.src = url.href;

    }


    /**
     *
     * @param item {HTMLElement} .item
     */
    itemClicked(item) {
        this.pos = parseInt(item.getAttribute("pos"));
        this.updateImage();
    }


    next() {

        this.pos++;

        if (this.pos >= this.gallery.itemsTotal) {
            this.pos = 0;
        }

        this.updateImage();

    }

    prev() {

        this.pos--;

        if (this.pos < 0) {
            this.pos = this.gallery.itemsTotal-1;
        }

        this.updateImage();
    }
}

document.imageGallery = new SellableImageGallery();
onPageLoad(function() {

    document.imageGallery.initialize();

});

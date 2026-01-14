class ImageSlider extends Component {
    static DOT_STYLE_SIMPLE = 0;
    static DOT_STYLE_IMAGE = 1;

    constructor() {
        super();
        this.currentIndex = 0;
        this.totalSlides = 0;
        this.autoPlayInterval = null;
        this.autoPlayDelay = 4000; // milliseconds
        this.container = null;
        this.viewport = null;
        this.prevBtn = null;
        this.nextBtn = null;
        this.dots = null;
        this.containerClass=".ImageSlider";
        this.viewportClass=".viewport";
        this.autoplayEnabled = true;
        this.dotStyle=ImageSlider.DOT_STYLE_SIMPLE;

        this.swipeListener = null;
    }
    updateSlider() {
        let translate = "-" + (this.currentIndex * 100) + "%";
        this.viewport.style.transform = "translateX(" + translate + ")";
        this.dots.forEach((dot, idx) => {
            //console.log(idx + "=> curr: "+ this.currentIndex);
            dot.classList.toggle('active', (idx === this.currentIndex))
        });
    }

    goToSlide(index) {
        this.currentIndex = index;
        this.updateSlider();
    }

    nextSlide() {
        this.currentIndex = (this.currentIndex + 1) % this.totalSlides;
        this.updateSlider();
    }

    prevSlide() {
        this.currentIndex = (this.currentIndex - 1 + this.totalSlides) % this.totalSlides;
        this.updateSlider();
    }

    startAutoPlay() {
        if (this.autoplayEnabled) {
            if (this.autoPlayInterval) clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = setInterval(() => this.nextSlide(), this.autoPlayDelay);
        }

    }

    stopAutoPlay() {
        if (this.autoplayEnabled) {
            if (this.autoPlayInterval) {
                clearInterval(this.autoPlayInterval);
                this.autoPlayInterval = null;
            }
        }
    }

    /**
     *
     * @param event {SparkEvent}
     */
    onEvent(event) {

        if (event.isEvent(SwipeListener.SWIPE_START)) {
            this.viewport.style.transition = 'none';
            if (document.imagePopup instanceof ImagePopup){
                document.imagePopup.enabled = true;
            }
            this.stopAutoPlay();
        }
        else if (event.isEvent(SwipeListener.SWIPE_MOVE)) {
            const offsetPercentage = (event.source.diff / this.container.clientWidth) * 100;
            const calc = "calc(-"+(this.currentIndex * 100) + "% + " + offsetPercentage + "%)";
            this.viewport.style.transform = "translateX(" + calc + ")";

            if (document.imagePopup instanceof ImagePopup){
                document.imagePopup.enabled = false;
            }
        }
        else if (event.isEvent(SwipeListener.SWIPE_LEFT)) {
            this.nextSlide();
        }
        else if (event.isEvent(SwipeListener.SWIPE_RIGHT)) {
            this.prevSlide();
        }
        else if (event.isEvent(SwipeListener.SWIPE_END)) {
            this.viewport.style.transition = 'transform 0.5s ease-in-out';
            this.updateSlider();
            this.startAutoPlay();
        }
        else if (event.isEvent(ImagePopup.EVENT_OPENED)) {
            this.stopAutoPlay();
        }
        else if (event.isEvent(ImagePopup.EVENT_CLOSED)) {
            this.currentIndex = event.source.pos;
            this.updateSlider();
            this.startAutoPlay();
        }
    }
    // itemClicked(index) {
    //     const bannerItem = this.viewport.children[index];
    //     if (bannerItem.hasAttribute('link')) {
    //         document.location.href = bannerItem.getAttribute('link');
    //     }
    // }
    initialize() {

        super.initialize();

        this.container = this.element.querySelector(this.containerClass);
        this.viewport = this.element.querySelector(this.viewportClass);

        this.swipeListener = new SwipeListener(this.viewport);
        this.swipeListener.threshold = this.container.clientWidth * 0.2;

        this.swipeListener.addObserver((event)=>this.onEvent(event));
        if (document.imagePopup instanceof ImagePopup){
            document.imagePopup.addObserver((event)=>this.onEvent(event));
        }

        // Pause auto-play when hovering over the banner (desktop)
        this.container.addEventListener('mouseenter', (event) => this.stopAutoPlay());
        this.container.addEventListener('mouseleave', (event) => {
            this.swipeListener.touchEndHandler(event);
            this.startAutoPlay()
        });


        this.totalSlides = this.viewport.children.length;

        if (this.totalSlides > 1) {
            let prevButton = document.createElement('div');
            prevButton.classList.add("nav-arrow");
            prevButton.classList.add("prev");
            prevButton.innerHTML = "&#10094;";
            this.container.appendChild(prevButton);

            let nextButton = document.createElement('div');
            nextButton.classList.add("nav-arrow");
            nextButton.classList.add("next");
            nextButton.innerHTML = "&#10095;";
            this.container.appendChild(nextButton);

            this.prevBtn = this.element.querySelector('.prev');
            this.prevBtn.addEventListener('click', () => this.prevSlide());

            this.nextBtn = this.element.querySelector('.next');
            this.nextBtn.addEventListener('click', () => this.nextSlide());

            //dots
            // Create navigation dots dynamically
            const dotsContainer = document.createElement('div');
            dotsContainer.className = 'dots';
            for (let i = 0; i < this.totalSlides; i++) {
                const dot = document.createElement('span');
                dot.className = 'dot';
                dot.addEventListener('click', () => this.goToSlide(i));
                if (this.dotStyle === ImageSlider.DOT_STYLE_IMAGE) {
                    dot.classList.add('image');
                    const img = document.createElement("img");
                    const parentImage = this.viewport.children[i].querySelector('img');
                    img.src = parentImage.src;
                    dot.appendChild(img);

                }
                dotsContainer.appendChild(dot);

                //append onClickHandler
                //this.viewport.children[i].addEventListener('click', () => this.itemClicked(i));

            }
            this.container.appendChild(dotsContainer);
        }

        this.dots = this.element.querySelectorAll('.dot');




        // Initial setup
        this.updateSlider();
        this.startAutoPlay();
    }
}













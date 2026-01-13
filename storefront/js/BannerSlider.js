
class BannerSlider extends Component {
    static STYLE_SIMPLE = 0;
    static STYLE_IMAGE = 1;

    constructor() {
        super();
        this.currentIndex = 0;
        this.totalSlides = 0;
        this.autoPlayInterval = null;
        this.autoPlayDelay = 4000; // milliseconds
        this.banners = null;
        this.viewport = null;
        this.prevBtn = null;
        this.nextBtn = null;
        this.dots = null;
        this.containerClass=".banners";
        this.viewportClass=".viewport";
        this.autoplayEnabled = true;
        this.dotStyle=BannerSlider.STYLE_SIMPLE;

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
    // itemClicked(index) {
    //     const bannerItem = this.viewport.children[index];
    //     if (bannerItem.hasAttribute('link')) {
    //         document.location.href = bannerItem.getAttribute('link');
    //     }
    // }
    initialize() {

        super.initialize();

        this.banners = this.element.querySelector(this.containerClass);
        this.viewport = this.element.querySelector(this.viewportClass);


        this.totalSlides = this.viewport.children.length;

        if (this.totalSlides > 1) {
            let prevButton = document.createElement('div');
            prevButton.classList.add("nav-arrow");
            prevButton.classList.add("prev");
            prevButton.innerHTML = "&#10094;";
            this.banners.appendChild(prevButton);

            let nextButton = document.createElement('div');
            nextButton.classList.add("nav-arrow");
            nextButton.classList.add("next");
            nextButton.innerHTML = "&#10095;";
            this.banners.appendChild(nextButton);

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
                if (this.dotStyle === BannerSlider.STYLE_IMAGE) {
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
            this.banners.appendChild(dotsContainer);
        }

        this.dots = this.element.querySelectorAll('.dot');

        // Pause auto-play when hovering over the banner (desktop)
        this.banners.addEventListener('mouseenter', (event) => this.stopAutoPlay());
        this.banners.addEventListener('mouseleave', (event) => {
            touchEndHandler(event);
            this.startAutoPlay()
        });


        // Touch support for mobile swipe gestures
        let isDragging = false;
        let startX = 0;

        const touchStartHandler = (e) => {
            startX = e.touches ? e.touches[0].clientX : e.x;
            isDragging = true;
            this.viewport.style.transition = 'none';
            if (document.imagePopup instanceof ImagePopup){
                document.imagePopup.enabled = true;
            }
            this.stopAutoPlay();
        };

        const touchMoveHandler = (e) => {
            if (!isDragging) return;
            const currentX = e.touches ? e.touches[0].clientX : e.x;
            const diff = currentX - startX;
            const offsetPercentage = (diff / this.banners.clientWidth) * 100;
            let calc = "calc(-"+(this.currentIndex * 100) + "% + " + offsetPercentage + "%)";
            this.viewport.style.transform = "translateX(" + calc + ")";
            e.preventDefault();
            if (document.imagePopup instanceof ImagePopup){
                document.imagePopup.enabled = false;
            }
        };

        const touchEndHandler = (e) => {
            if (!isDragging) return;
            isDragging = false;
            this.viewport.style.transition = 'transform 0.5s ease-in-out';

            const endX = e.changedTouches ? e.changedTouches[0].clientX : e.x;
            const diff = endX - startX;
            const threshold = this.banners.clientWidth * 0.2; // 20% of slider width

            if (Math.abs(diff) > threshold) {
                if (diff < 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            } else {
                this.updateSlider(); // Snap back to current slide
            }

            this.startAutoPlay();
        };

        this.viewport.addEventListener('touchstart', touchStartHandler, { passive: true });
        this.viewport.addEventListener('touchmove', touchMoveHandler, { passive: false });
        this.viewport.addEventListener('touchend', touchEndHandler);

        this.viewport.addEventListener('mousedown', touchStartHandler, { passive: true });
        this.viewport.addEventListener('mousemove', touchMoveHandler, { passive: false });
        this.viewport.addEventListener('mouseup', touchEndHandler);

        // Initial setup
        this.updateSlider();
        this.startAutoPlay(); // Auto-play enabled by default
    }
}













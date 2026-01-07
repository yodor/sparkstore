
class BannerSlider extends Component {
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
        if (this.autoPlayInterval) clearInterval(this.autoPlayInterval);
        this.autoPlayInterval = setInterval(() => this.nextSlide(), this.autoPlayDelay);
    }

    stopAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = null;
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

        this.banners = this.element.querySelector('.banners');
        this.viewport = this.element.querySelector('.viewport');


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
        }




        // Create navigation dots dynamically
        const dotsContainer = document.createElement('div');
        dotsContainer.className = 'dots';
        for (let i = 0; i < this.totalSlides; i++) {
            const dot = document.createElement('span');
            dot.className = 'dot';
            dot.addEventListener('click', () => this.goToSlide(i));
            dotsContainer.appendChild(dot);

            //append onClickHandler
            //this.viewport.children[i].addEventListener('click', () => this.itemClicked(i));

        }
        this.banners.appendChild(dotsContainer);


        this.dots = this.element.querySelectorAll('.dot');




        // Pause auto-play when hovering over the banner (desktop)
        this.banners.addEventListener('mouseenter', () => this.stopAutoPlay());
        this.banners.addEventListener('mouseleave', () => this.startAutoPlay());


        // Touch support for mobile swipe gestures
        let isDragging = false;
        let startX = 0;

        const touchStartHandler = (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
            this.viewport.style.transition = 'none';
            this.stopAutoPlay();
        };

        const touchMoveHandler = (e) => {
            if (!isDragging) return;
            const currentX = e.touches[0].clientX;
            const diff = currentX - startX;
            const offsetPercentage = (diff / this.banners.clientWidth) * 100;
            let calc = "calc(-"+(this.currentIndex * 100) + "% + " + offsetPercentage + "%)";
            this.viewport.style.transform = "translateX(" + calc + ")";
            e.preventDefault();
        };

        const touchEndHandler = (e) => {
            if (!isDragging) return;
            isDragging = false;
            this.viewport.style.transition = 'transform 0.5s ease-in-out';

            const endX = e.changedTouches[0].clientX;
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

        this.banners.addEventListener('touchstart', touchStartHandler, { passive: true });
        this.banners.addEventListener('touchmove', touchMoveHandler, { passive: false });
        this.banners.addEventListener('touchend', touchEndHandler);

        // Initial setup
        this.updateSlider();
        this.startAutoPlay(); // Auto-play enabled by default
    }
}













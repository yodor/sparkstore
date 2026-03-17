
class ImageFader extends Component
{
    constructor() {
        super();
        this.containerClass = ".banners";
        this.viewportClass = ".viewport";
        this.timerID = 0;
        this.running = false;
    }

    initialize() {
        super.initialize();

        this.container = this.element.querySelector(this.containerClass);
        this.viewport = this.element.querySelector(this.viewportClass);

    }

    /**
     * Call after initialize to start the fading
     */
    setupFade()
    {
        if (this.running) return;

        this.container.classList.remove("fade");
        this.container.classList.add("fade");

        this.start();
    }

    /**
     * Delay start the fading default 3000 ms
     * @param timeout{int}
     */
    setupFadeDelayed(timeout) {
        if (this.running) return;
        setTimeout(() => this.setupFade(), timeout);
    }


    stop() {
        this.running = false;
        if(this.timerID>0) clearTimeout(this.timerID);
        this.timerID = 0;
    }

    start()
    {
        if (this.running) return;

        if (this.timerID === 0) {
            this.running = true;
            //initial run - let fadeBanner restart itself if running flag is true
            this.timerID = setTimeout(()=>this.fadeBanner(), this.getTimeout());
        }
    }

    getTimeout()
    {
        return (((Math.random()+1) * 30)) * 100;
    }

    /**
     * Target the viewport elements
     */
    fadeBanner()
    {
        if (!this.running) return;

        let first = this.viewport.childNodes.item(0);
        if (first instanceof HTMLElement) {
            this.viewport.appendChild(this.viewport.removeChild(first));
            if (this.running) {
                this.timerID = setTimeout(()=>this.fadeBanner(), this.getTimeout());
            }
        }
    }

}
/**
 * Loads the product tapes of NavigationList items when they come near the viewport.
 * The placeholders (.TapePlaceholder) carry the responder, the navigation list index and the item value;
 * placeholders of one list that show up together are loaded with one request.
 * Backend: NavigationTapeResponder (store/responders/json)
 */
class NavigationTapes {

    constructor() {
        /** @type {Map<string, HTMLElement[]>} list index -> placeholders waiting for the request */
        this.pending = new Map();
        this.timer = null;
        //start loading one screen ahead
        this.observer = new IntersectionObserver((entries) => this.onIntersect(entries), {rootMargin: "100% 0px"});
    }

    initialize() {
        document.querySelectorAll(".TapePlaceholder[item]").forEach((placeholder) => this.observer.observe(placeholder));
    }

    /**
     * @param entries {IntersectionObserverEntry[]}
     */
    onIntersect(entries) {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            this.observer.unobserve(entry.target);
            const list = entry.target.getAttribute("list");
            if (!this.pending.has(list)) this.pending.set(list, []);
            this.pending.get(list).push(entry.target);
        });
        //collect the placeholders that become visible together
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.load(), 50);
    }

    load() {
        this.pending.forEach((placeholders, list) => {
            const request = new JSONRequest();
            request.setResponder(placeholders[0].getAttribute("responder"));
            request.setFunction("render");
            request.setParameter("list", list);
            request.setParameter("items", placeholders.map((placeholder) => placeholder.getAttribute("item")).join(","));
            request.onSuccess = (result) => this.place(placeholders, result.response.contents);
            request.onError = () => placeholders.forEach((placeholder) => placeholder.remove());
            request.start();
        });
        this.pending.clear();
    }

    /**
     * Replace each placeholder with its tape; an item left without tape and banners is removed
     * @param placeholders {HTMLElement[]}
     * @param html {string} TapeResult elements - one per requested item
     */
    place(placeholders, html) {
        const results = document.templateFactory.nodeList(html);
        placeholders.forEach((placeholder) => {
            const value = placeholder.getAttribute("item");
            const result = results.find((node) => node.getAttribute && node.getAttribute("item") === value);
            const item = placeholder.closest(".NavigationListItem");
            if (result && result.children.length > 0) {
                placeholder.replaceWith(...result.children);
            }
            else {
                placeholder.remove();
                if (item && !item.querySelector(".banners .item")) item.remove();
            }
            if (item) {
                const event = new SparkEvent(SparkEvent.DOM_UPDATED);
                event.source = item;
                document.dispatchEvent(event);
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (!("IntersectionObserver" in window)) return;
    document.navigationTapes = new NavigationTapes();
    document.navigationTapes.initialize();
});

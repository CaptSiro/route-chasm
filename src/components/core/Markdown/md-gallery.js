class Gallery {
    /** @type {number} */
    #cursor = 0;
    /** @type {MarkdownGalleryImage[]} */
    #images = [];
    /** @type {HTMLElement} */
    #window;
    /** @type {HTMLElement} */
    #slides;
    /** @type {HTMLElement} */
    #thumbnails;



    #createWindow() {
        if (is(this.#window)) {
            return this.#window;
        }

        const { div } = jsml;
        this.#slides = div('slides');

        let startX = 0;
        let currentTranslate = 0;
        let isDragging = false;

        const touchStart = x => {
            startX = x;
            isDragging = true;
        }

        const touchMove = x => {
            if (!isDragging) {
                return;
            }

            const diff = x - startX;
            this.#slides.style.transition = 'none';
            this.#slides.style.transform = `translateX(calc(-${this.#cursor * 100}vw + ${diff}px))`;
        }

        const touchEnd = x => {
            if (!isDragging) {
                return;
            }

            const diff = x - startX;
            isDragging = false;
            this.#slides.style.transition = 'transform 0.4s ease';

            if (diff > 100) {
                this.goPrevious();
            } else if (diff < -100) {
                this.goNext();
            } else {
                this.#slides.style.transform = `translateX(-${this.#cursor * 100}vw)`;
            }
        }

        this.#slides.addEventListener('mousedown', e => touchStart(e.clientX));
        this.#slides.addEventListener('mousemove', e => touchMove(e.clientX));
        this.#slides.addEventListener('mouseup', e => touchEnd(e.clientX));
        this.#slides.addEventListener('mouseleave', e => touchEnd(e.clientX));

        this.#slides.addEventListener('touchstart', e => touchStart(e.touches[0].clientX));
        this.#slides.addEventListener('touchmove', e => touchMove(e.touches[0].clientX));
        this.#slides.addEventListener('touchend', e => touchEnd(e.changedTouches[0].clientX));

        this.#thumbnails = div('thumbnails');

        return this.#window = window_create(
            'Gallery',
            div('md-gallery', [
                div('viewport', [
                    this.#slides,
                    div(
                        { class: 'nav left', onClick: () => this.goPrevious() },
                        Icon('nf-fa-angle_left', 'Left')
                    ),
                    div(
                        { class: 'nav right', onClick: () => this.goNext() },
                        Icon('nf-fa-angle_right', 'Right')
                    ),
                ]),
                this.#thumbnails
            ]),
            {
                isDialog: true,
                isMinimizable: false,
                width: '100vw',
                height: '100vh',
                isDraggable: false,
                isResizable: false
            }
        );
    }

    /**
     * @param {MarkdownGalleryImage} image
     */
    #createSlide(image) {
        return jsml.div('slide',
            jsml.img(image)
        );
    }

    /**
     * @param {MarkdownGalleryImage} image
     */
    #createThumbnail(image) {
        const id = std_id_html(8);
        const onClick = event => {
            const target = event.target.classList?.contains('thumb')
                ? event.target
                : event.target.closest('.thumb');

            if (!is(target)) {
                return;
            }

            for (let i = 0; i < this.#thumbnails.children.length; i++) {
                const thumb = this.#thumbnails.children[i];
                if (thumb.id !== id) {
                    continue;
                }

                this.goTo(i);
            }
        };

        return jsml.div(
            { id, class: 'thumb', onClick },
            jsml.img(image)
        );
    }

    show() {
        window_open(this.#window);
    }

    close() {
        window_close(this.#window);
    }

    add(src, alt, title = undefined) {
        this.#createWindow();

        const image = { src, alt, title };
        this.#images.push(image);
        this.#slides.append(this.#createSlide(image));
        this.#thumbnails.append(this.#createThumbnail(image));

        return this.#images.length - 1;
    }

    goTo(cursor) {
        this.#thumbnails.children[this.#cursor]?.classList.remove('active');

        this.#cursor = (cursor + this.#images.length) % this.#images.length;

        this.#slides.style.transform = `translateX(-${this.#cursor * 100}vw)`;
        this.#thumbnails.children[this.#cursor]?.classList.add('active');
    }

    goNext() {
        this.goTo(this.#cursor + 1);
    }

    goPrevious() {
        this.goTo(this.#cursor - 1);
    }
}
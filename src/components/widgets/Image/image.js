class WImage extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef ImageJSONType
     * @property {string=} src
     * @property {string=} alt
     * @property {number=} width
     * @property {number=} height
     * @property {number=} borderRadius
     * @property {number=} aspectRatio
     * @property {[number, number]=} position
     *
     * @typedef {ImageJSONType & WidgetJSON} ImageJSON
     */

    /**
     * @type {Observable<ImageJSON>}
     */
    #json;
    #resizeable;
    /**
     * @type {HTMLImageElement}
     */
    #imageElement;
    #imageContainer;
    // [width(%), height(px), border-radius(%), aspect-ratio(width(px) / height(px) | null)]
    #dimensions = new Observable([0, 0, 0, 0]);
    #position;
    static VIEWPORT_MARGIN = 16;


    /**
     * @param {ImageJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        const image = Img(
            json.src !== undefined
                ? WImage.createSourceURL(json.src)
                : AJAX.SERVER_HOME + "/public/images/backgrounds-mono/0.png",
            json.alt ?? "Unnamed image",
            "w-image",
            { attributes: { draggable: "false" } }
        );
        const imageContainer = Div("w-image-container", image);
        super(
            Div("w-image-mount", imageContainer),
            parent,
            editable
        );

        this.rootElement.style.userSelect = "none";
        this.#imageElement = image;
        this.#imageContainer = imageContainer;
        this.childSupport = "none";

        json.position = (json.position ?? [50, 50])
            .filter(any => typeof any === "number")
            .map(number => clamp(0, 100, number));

        this.#position = json.position ?? [50, 50];
        this.#imageElement.style.objectPosition = this.#position[0] + "% " + this.#position[1] + "%";


        if (editable) {
            this.#json = new Observable(json);
            this.#json.onChange(descriptor => {
                this.#imageElement.src = WImage.createSourceURL(descriptor.src);
            });

            this.#resizeable = new Resizeable(imageContainer, { axes: "diagonal", borderRadius: "enabled" });
            this.#resizeable.on("resize", (width, height) => {
                const oldDimensions = this.#dimensions.value;
                this.#dimensions.value = [
                    clamp(0.05, 1, width / this.rootElement.getBoundingClientRect().width),
                    clamp(16, 4096, height),
                    oldDimensions[2]
                ];
            });
            this.#resizeable.on("radius", borderRadius => {
                this.#dimensions.setProperty(2, borderRadius);
            });
            this.#resizeable.content.style.width = "unset";
            this.#resizeable.content.style.height = "unset";


            const imagePositioner = evt => {
                const width = this.#dimensions.value[0] * this.rootElement.getBoundingClientRect().width;
                const height = this.#dimensions.value[1];
                const aspectRatio = width / height;
                const naturalAspectRatio = this.#imageElement.naturalWidth / this.#imageElement.naturalHeight;

                if (aspectRatio > naturalAspectRatio) {
                    // recalculate only y object position
                    this.#position[1] = clamp(0, 100, this.#position[1] - (evt.movementY / (aspectRatio * naturalAspectRatio / 2.5)));
                } else {
                    // recalculate only x object position
                    this.#position[0] = clamp(0, 100, this.#position[0] - (evt.movementX * (aspectRatio / naturalAspectRatio / 2.5)));
                }

                this.#imageElement.style.objectPosition = this.#position[0] + "% " + this.#position[1] + "%";
            };
            this.#imageElement.addEventListener("pointerdown", evt => {
                this.#imageElement.setPointerCapture(evt.pointerId);

                this.#imageElement.addEventListener("pointermove", imagePositioner);
                this.#imageElement.addEventListener("pointerup", () => {
                    this.#imageElement.removeEventListener("pointermove", imagePositioner);
                });
            });
            this.#imageElement.classList.add("move-able");

            this.appendEditGui();
        } else {
            this.#imageContainer.style.overflow = "hidden";
        }


        this.#dimensions.onChange(([width, height, borderRadius]) => {
            const oldDimensions = this.#dimensions.value;

            oldDimensions[3] =
                (this.rootElement.getBoundingClientRect().width * width) / height;
            this.#dimensions.setValueSafe(oldDimensions);

            this.setImageDimensions(width, height, borderRadius);
        });

        new Promise(resolve => {
            const id = guid(true);
            this.rootElement.id = id;
            untilElement("#" + id)
                .then(() => {
                    this.rootElement.id = undefined;
                    freeID(id);
                    resolve();
                });
        }).then(() => this.#dimensions.value = [clamp(0.05, 1, json.width) ?? 0.70, clamp(16, 4096, json.height) ?? 400, clamp(0, 50, json.borderRadius) ?? 0, json.aspectRatio]);


        /**
         * @param {ViewportDimensions} dimensions
         */
        const resizeListener = async dimensions => {
            if (this.#dimensions.value[3] === undefined || typeof this.#dimensions.value[3] !== "number") return;

            if (editable) {
                await sleep((dimensions.duration ?? 250) + 10);
            }

            const oldDimensions = this.#dimensions.value;
            oldDimensions[1] =
                (this.rootElement.getBoundingClientRect().width * oldDimensions[0]) / this.#dimensions.value[3];

            this.#dimensions.setValueSafe(oldDimensions);
            this.setImageDimensions(oldDimensions[0], oldDimensions[1], oldDimensions[2]);
        };

        onViewportResize(resizeListener);

        if (viewportDimensions !== undefined) {
            resizeListener(viewportDimensions);
            return;
        }

        viewportResize();
    }

    setImageDimensions(widthPercentage, height, borderRadius) {
        this.#imageContainer.style.width = (widthPercentage * 100) + "%";

        if (this.editable === false) {
            this.#imageContainer.style.height = height + "px";
            this.#imageContainer.style.borderRadius = borderRadius + "%";
            return;
        }

        this.#resizeable.content.style.height = height + "px";
        this.#resizeable.setRadiusHandlesPositions(borderRadius);

        this.#resizeable.content.animate({
            borderRadius: borderRadius + "%"
        }, {
            duration: 500,
            fill: "forwards"
        });
    }

    /**
     * @param {string} src
     * @return {string}
     */
    static createSourceURL(src) {
        return `${AJAX.SERVER_HOME}/file/${webpage.src}/${src}`;
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WImage}
     */
    static default(parent, editable = false) {
        return this.build({
            alt: "Unnamed image",
            width: 0.75,
            height: 400
        }, parent, editable);
    }

    /**
     * @override
     * @param {ImageJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WImage}
     */
    static build(json, parent, editable = false) {
        return new WImage(json, parent, editable);
    }

    /**
     * @override
     * @returns {ComponentContent}
     */
    get inspectorHTML() {
        return [
            TitleInspector("Image"),

            HRInspector(),

            TitleInspector("Properties"),

            TextFieldInspector(this.#json.value.alt, (value, parentElement) => {
                this.#json.setProperty("alt", value);
                validated(parentElement);
                return true;
            }, "Text description:"),

            Div("i-row", [
                Span(__, "Image:"),
                Button("button-like-main", "Select", evt => {
                    const win = showWindow("file-select");
                    win.dataset.multiple = "false";
                    win.dataset.fileType = "image";
                    win.dispatchEvent(new Event("fetch"));
                    win.onsubmit = submitEvent => {
                        this.#json.setProperty("src", submitEvent.detail[0].serverName);
                        validated(evt.target.parentElement);
                    };
                })
            ])
        ];
    }

    /**
     * @override
     * @returns {WidgetJSON}
     */
    save() {
        return {
            type: "WImage",
            src: this.#json.value.src,
            alt: this.#json.value.alt,
            width: this.#dimensions.value[0],
            height: this.#dimensions.value[1],
            borderRadius: this.#dimensions.value[2],
            aspectRatio: this.#dimensions.value[3],
            position: this.#position
        };
    }

    focus() {
        inspect(this.inspectorHTML, this);
    }
}



widgets.define("WImage", WImage);
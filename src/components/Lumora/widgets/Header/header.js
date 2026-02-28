class WHeader extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef HeaderJSONType
     * @property {"start" | "center" | "end"=} titleAlign
     * @property {string=} titleColor
     *
     * @typedef {HeaderJSONType & WidgetJSON} HeaderJSON
     */

    /**
     * @param {HeaderJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(jsml.div("w-header center"), parent, editable);
        this.removeMargin();
        this.childSupport = this.childSupport;

        const localization = api_loadLocalization();
        const title = localization?.title ?? "Title";

        const headingContainer = (
            jsml.div(
                {
                    style: {
                        textAlign: json.titleAlign ?? "center",
                        color: json.titleColor ?? "var(--text-color-0)"
                    },
                    class: "heading-container"
                },
                [
                    jsml.h1({
                        class: "page-title",
                        title: editable
                            ? "Edit->Properties->Title"
                            : title
                    }, title),
                    Optional(is(localization?.releaseDate), jsml.span(_, localization?.releaseDate))
                ]
            )
        );

        const root = this.getRoot();
        if (root.json?.isHeaderIncluded === false) {
            this.rootElement.classList.add("display-none");
        }

        if (is(root.json.headerImageSrc)) {
            this.rootElement.style.backgroundImage = `url(${root.json.headerImageSrc})`;
        }

        root.addJSONListener?.call(root, json => {
            this.rootElement.classList.toggle("display-none", !json.isHeaderIncluded);

            if (is(json.headerImageHash)) {
                const src = WImage.createSource(json.headerImageHash, json.headerImageVariant);
                json.headerImageSrc = src;
                this.rootElement.style.backgroundImage = `url(${src})`;
            }

            headingContainer.style.textAlign = json.headerTitleAlign ?? "center";
            headingContainer.style.color = json.headerTitleColor ?? "var(--text-color-0)";
        });

        this.rootElement.appendChild(headingContainer);

        if (editable) {
            const resizeListener = dimensions => {
                this.rootElement.animate({
                    width: dimensions.width + "px",
                    height: dimensions.height + "px"
                }, {
                    duration: dimensions.duration ?? 250,
                    fill: "forwards"
                });
            };

            lumora_viewport_onResize(resizeListener);

            if (lumora_viewport_dimension !== undefined) {
                resizeListener(lumora_viewport_dimension);
                return;
            }

            lumora_viewport_resize();
        }
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WHeader}
     */
    static default(parent, editable = false) {
        return new WHeader({}, parent, editable);
    }

    /**
     * @override
     * @param {HeaderJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WHeader}
     */
    static build(json, parent, editable = false) {
        return new WHeader(json, parent, editable);
    }

    /**
     * @override
     * @returns {Content}
     */
    get inspectorHTML() {
        return (
            NotInspectorAble()
        );
    }

    /**
     * @override
     * @returns {WidgetJSON}
     */
    save() {
        return {
            type: "WHeader"
        };
    }

    isSelectAble() {
        return false;
    }
}



widgets.define("WHeader", WHeader);
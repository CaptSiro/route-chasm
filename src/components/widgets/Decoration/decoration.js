class WTextDecoration extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef TextDecorationJSONType
     * @property {string=} text
     * @property {string[]=} class
     *
     * @typedef {TextDecorationJSONType & WidgetJSON} TextDecorationJSON
     */

    /**
     * @param {TextDecorationJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(jsml.span("w-text-decoration", String(json.text)), parent, editable);
        this.removeMargin();

        if (json.class) {
            for (const clazz of json.class) {
                this.rootElement.classList.add(clazz);
            }
        }

        this.childSupport = this.childSupport;
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WTextDecoration}
     */
    static default(parent, editable = false) {
        return new WTextDecoration({}, parent, editable);
    }

    /**
     * @override
     * @param {TextDecorationJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WTextDecoration}
     */
    static build(json, parent, editable = false) {
        return new WTextDecoration(json, parent, editable);
    }

    static unpack(descriptionArray, parent, editable = false) {
        const classes = Object.values(WTextDecoration.types);
        return new WTextDecoration({
            text: descriptionArray.shift(),
            class: descriptionArray.map(index => classes[index])
        }, parent, editable);
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

    isSelectAble() {
        return false;
    }

    /**
     * @override
     * @returns {WidgetJSON}
     */
    save() {
        return {
            type: "WTextDecoration",
            text: this.rootElement.textContent,
            class: this.getFilteredClasses()
        };
    }

    // saveEfficiently () {
    //   const classes = this.getFilteredClasses();
    //   if (classes.length === 0) {
    //     return this.rootElement.textContent;
    //   }
    //
    //   return this.file_save();
    // }

    saveCompact() {
        console.log("compact");
        this.rootElement.normalize();
        const classes = this.getFilteredClasses();
        const decorationsAndWidgets = [];

        const indexes = classes.map(c => WTextDecoration.#typesCompatibilityIndexes[c]);
        for (const child of this.rootElement.childNodes) {
            if (child.nodeName === "BR") continue;

            if (child.widget !== undefined) {
                decorationsAndWidgets.push(child?.widget.save());
                continue;
            }

            decorationsAndWidgets.push(
                indexes.length === 0
                    ? child.textContent
                    : [child.textContent, ...indexes]
            );
        }

        return decorationsAndWidgets;
    }

    focus() {
        this.focusWhole(true);
    }



    static types = Object.freeze({
        BOLD: "bold",
        ITALIC: "italic",
        UNDERLINE: "underline",
        LINE_THROUGH: "line-through",
        CODE: "code"
    });

    static #typesCompatibility = {
        "bold": [0, 1, 1, 1, 0],
        "italic": [1, 0, 1, 1, 0],
        "underline": [1, 1, 0, 0, 0],
        "line-through": [1, 1, 0, 0, 0],
        "code": [0, 0, 0, 0, 0]
    };
    static #typesCompatibilityIndexes = {
        "bold": 0,
        "italic": 1,
        "underline": 2,
        "line-through": 3,
        "code": 4
    };

    getFilteredClasses() {
        const types = Object.values(WTextDecoration.types);
        return Array.from(this.rootElement.classList.values())
            .filter(c => types.includes(c));
    }

    /**
     * @param {"bold", "italic", "underline", "line-through", "code", "none"} type
     * @param {boolean} setState
     */
    toggleDecoration(type, setState = undefined) {
        if (this.rootElement.classList.contains(type)) {
            if (!(setState === undefined || setState === false)) return;

            this.rootElement.classList.remove(type);
            return;
        }

        if (type === "none" || type === undefined) {
            for (const removeType in WTextDecoration.types) {
                this.rootElement.classList.remove(removeType);
            }
            return;
        }

        const addingTypeIndex = WTextDecoration.#typesCompatibilityIndexes[type];
        if (addingTypeIndex === undefined) {
            return;
        }

        const toRemove = [];
        this.rootElement.classList.forEach(value => {
            if (WTextDecoration.#typesCompatibility[type][WTextDecoration.#typesCompatibilityIndexes[value]] === 0) {
                toRemove.push(value);
            }
        });

        if (setState === undefined || setState === true) {
            for (const toRemoveClass of toRemove) {
                this.rootElement.classList.remove(toRemoveClass);
            }

            this.rootElement.classList.add(type);
        }
    }


    /**
     * @param {boolean} isAnchor
     * @param {boolean} isFocus
     * @param {number} startOffset
     * @param {number} endOffset
     */
    split(isAnchor, isFocus, startOffset, endOffset) {
        if (isAnchor !== true && isFocus !== true) {
            return {
                elements: [this],
                index: 0
            };
        }

        const offset = isAnchor ? startOffset : endOffset;
        const classes = this.getFilteredClasses();

        return {
            elements: [
                WTextDecoration.build({
                    text: this.rootElement.textContent.substring(0, offset),
                    class: classes
                }, this.parentWidget),
                WTextDecoration.build({
                    text: this.rootElement.textContent.substring(offset),
                    class: classes
                }, this.parentWidget)
            ],
            index: !isAnchor ? 0 : 1
        };
    }



    focusWhole(collapse = false) {
        const range = document.createRange();
        const selection = window.getSelection();

        if (this.rootElement.textContent === "") {
            this.rootElement.innerHTML = "&#8203;";
        }

        const text = this.rootElement.childNodes[0];

        range.setStart(text, 0);
        range.setEnd(text, text.textContent.length);

        if (collapse) {
            range.collapse(false);
        }
        selection.removeAllRanges();
        selection.addRange(range);
    }
}



widgets.define("WTextDecoration", WTextDecoration);
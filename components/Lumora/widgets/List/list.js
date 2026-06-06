class WList extends ContainerWidget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef {"disc" | "circle" | "decimal" | "square" | "lower-alpha" | "upper-alpha" | "lower-roman" | "upper-roman" | "hiragana" | "katakana" | undefined} ListStylesTypes
     */
    /**
     * @typedef ListJSONType
     * @property {TextEditorJSONContent[]=} items
     * @property {ListStylesTypes} listStyleType
     *
     * @typedef {ListJSONType & WidgetJSON} ListJSON
     */

    #json;

    /**
     * @param {ListJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(jsml.ul("w-list"), parent, editable, WListItem, false);
        this.childSupport = this.childSupport;
        this.createConfinedContainer();

        this.#json = json;

        if (editable) {
            this.appendEditGui();
        }

        if (json.items === undefined || json.items?.length === 0) {
            this.appendWidget(WListItem.default(this, editable));
            return;
        }

        this.#addItems(json.items).then();

        if (json.type) {
            this.rootElement.style.setProperty("--type", json.listStyleType);
        }
    }

    /**
     * @param {TextEditorJSONContent} items
     * @returns {Promise<void>}
     */
    async #addItems(items) {
        for (const item of items) {
            await this.appendWidget(WListItem.build({
                text: item
            }, this, this.editable));
        }
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WList}
     */
    static default(parent, editable = false) {
        return new WList({}, parent, editable);
    }

    /**
     * @override
     * @param {ListJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WList}
     */
    static build(json, parent, editable = false) {
        return new WList(json, parent, editable);
    }

    /**
     * @override
     * @returns {Content}
     */
    get inspectorHTML() {
        return [
            TitleInspector("List"),

            HRInspector(),

            TitleInspector("Properties"),
            SelectInspector(value => {
                this.#json.listStyleType = value;
                this.rootElement.style.setProperty("--type", value);
                return true;
            }, selectOption([
                { text: "Circle", value: "circle" },
                { text: "Filled Circle", value: "disc" },
                { text: "Square", value: "square" },
                { text: "1. 2. 3.", value: "decimal" },
                { text: "a. b. c.", value: "lower-alpha" },
                { text: "A. B. C.", value: "upper-alpha" },
                { text: "i. ii. iii.", value: "lower-roman" },
                { text: "I. II. III.", value: "upper-roman" },
                { text: "Hiragana", value: "hiragana" },
                { text: "Katakana", value: "katakana" }
            ], this.#json.listStyleType, "disc"), "Style", "large")
        ];
    }

    removeWidget(widget, doRemoveFromRootElement = true, doAnimate = true) {
        const removedIndex = this.children.indexOf(widget);
        let hasBeenRemoved = super.removeWidget(widget, doRemoveFromRootElement, doAnimate);

        if (!doRemoveFromRootElement) return false;
        if (typeof hasBeenRemoved === "boolean") {
            hasBeenRemoved = Promise.resolve(hasBeenRemoved);
        }

        return hasBeenRemoved
            .then(boolean => {
                if (boolean === false) return false;
                if (removedIndex === 0) {
                    if (this.children.length > 0) {
                        this.children[0].focus();
                    }
                    return true;
                }

                this.children[removedIndex - 1].focus();
            });
    }

    /**
     * @override
     * @returns {WidgetJSON}
     */
    save() {
        return {
            type: "WList",
            items: this.children.map(child => child.saveCompact?.call(child)),
            listStyleType: this.#json.listStyleType
        };
    }

    focus() {
        editor_inspect(this.inspectorHTML, this);
        this.children[this.children.length - 1].focus();
    }

    nextDefault(after) {
        const added = super.nextDefault(after);
        added.focus();
        return added;
    }

    placeCommandBlock(after) {
        if (document?.widgetElement.editable !== true) {
            return;
        }

        const indexOfAfter = this.children.indexOf(after);

        const listItem = WListItem.default(this, this.editable);
        this.children.splice(indexOfAfter + 1, 0, listItem);

        if (indexOfAfter + 2 === this.children.length) {
            this.rootElement.appendChild(listItem.rootElement);
        } else {
            this.rootElement.insertBefore(listItem.rootElement, this.children[indexOfAfter + 2].rootElement);
        }

        listItem.focus();
    }
}



widgets.define("WList", WList);
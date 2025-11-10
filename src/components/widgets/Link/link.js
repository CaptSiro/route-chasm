class WLink extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef LinkJSONType
     * @property {string} url
     * @property {string=} text
     * @property {string=} title
     * @property {boolean=} useOppositeColors
     *
     * @typedef {LinkJSONType & WidgetJSON} LinkJSON
     */

    static #urlRegex = /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.\S{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.\S{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.\S{2,}|www\.[a-zA-Z0-9]+\.\S{2,})/;

    /**
     * @param {string} string
     */
    static isValidLink(string) {
        return this.#urlRegex.test(string);
    }

    #json;

    /**
     * @param {LinkJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(
            Link(json.url, "w-link", String(json.text ?? json.title ?? json.url), {
                attributes: {
                    target: "_blank",
                    title: json.title ?? "",
                    contenteditable: "false"
                }
            }),
            parent,
            editable
        );
        this.removeMargin();
        this.childSupport = "none";

        this.#json = json;
        this.useOppositeColors(json.useOppositeColors ?? false);

        if (!editable) {
            this.rootElement.classList.add("not-edit");
        }

        this.rootElement.addEventListener("click", evt => {
            document.body?.classList.remove("cursor-pointer");
            if ((evt.ctrlKey || editable === false) && confirm("Do you want to open this link?\n" + json.url)) return;

            evt.preventDefault();
            evt.stopImmediatePropagation();
        });
    }

    useOppositeColors(bool) {
        this.rootElement.classList.toggle("opposite", bool);
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WText}
     */
    static default(parent, editable = false) {
        return WText.build({
            textEditor: {
                content: [[{
                    type: "WLink",
                    url: "",
                    text: "link",
                    title: "link"
                }]]
            }
        }, parent, editable);
    }

    /**
     * @override
     * @param {LinkJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WLink}
     */
    static build(json, parent, editable = false) {
        return new WLink(json, parent, editable);
    }

    /**
     * @override
     * @returns {ComponentContent}
     */
    get inspectorHTML() {
        return [
            TitleInspector("Link"),

            HRInspector(),

            TextFieldInspector(this.#json.text, (value, parentElement) => {
                this.#json.text = value.replace("\n", "");
                this.rootElement.textContent = this.#json.text;
                validated(parentElement);
                return true;
            }, "Label:"),
            TextFieldInspector(this.#json.url, (value, parentElement) => {
                if (!WLink.isValidLink(value)) {
                    rejected(parentElement);
                    return false;
                }

                this.#json.url = value;
                this.rootElement.setAttribute("href", value);
                validated(parentElement);
                return true;
            }, "URL:"),
            TextFieldInspector(this.#json.title, (value, parentElement) => {
                this.#json.title = value.replace("\n", "");
                this.rootElement.setAttribute("title", this.#json.title);
                validated(parentElement);
                return true;
            }, "Tooltip:")
        ];
    }

    /**
     * @override
     * @returns {WidgetJSON}
     */
    save() {
        return {
            type: "WLink",
            text: this.#json.text,
            title: this.#json.title,
            url: this.#json.url
        };
    }

    focus() {
        inspect(this.inspectorHTML, this);
    }

    isSelectAble() {
        return false;
    }

    isSelectionPropagable() {
        return false;
    }
}



window.addEventListener("keydown", evt => {
    if (evt.ctrlKey === false) return;
    document.body?.classList.add("cursor-pointer");
});
window.addEventListener("keyup", evt => {
    if (evt.ctrlKey === true) return;
    document.body?.classList.remove("cursor-pointer");
});
widgets.define("WLink", WLink);
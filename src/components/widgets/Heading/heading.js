class WHeading extends Widget { // var is used because it creates reference on globalThis (window) object

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef HeadingJSONType
     * @property {string} text
     * @property {number} level
     *
     * @typedef {HeadingJSONType & WidgetJSON} HeadingJSON
     */

    /**
     * @param {HeadingJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(
            jsml['h' + std_clamp(1, 6, json.level ?? 3)]("w-heading"),
            parent,
            editable
        );
        this.childSupport = 1;
        this.removeInspectHandler();

        this.appendWidget(WTextEditor.build({
            content: [json.text],
            forceSingleLine: true,
            mode: "simple",
            disableLinks: true
        }, this, editable));

        if (editable) {
            this.appendEditGui();
        }
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WHeading}
     */
    static default(parent, editable = false) {
        return this.build({ level: 3, text: "Lorem ipsum" }, parent, editable);
    }

    /**
     * @override
     * @param {HeadingJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WHeading}
     */
    static build(json, parent, editable = false) {
        return new WHeading(json, parent, editable);
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
            type: "WHeading",
            level: Number(this.rootElement.tagName[1]),
            text: this.children[0].rootElement.textContent
        };
    }
}



widgets.define("WHeading", WHeading);
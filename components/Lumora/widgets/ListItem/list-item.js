class WListItem extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef ListItemJSONType
     * @property {TextEditorJSONContent} text
     *
     * @typedef {ListItemJSONType & WidgetJSON} ListItemJSON
     */

    #textEditor;

    /**
     * @param {ListItemJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(jsml.li("w-list-item"), parent, editable);
        this.childSupport = 1;
        this.removeMargin();

        this.#textEditor = WTextEditor.build({
            content: json.text,
            mode: "simple",
            doRequestNewLine: true
        }, this, editable);

        this.#textEditor.addListener("remove", () => this.remove());
        this.#textEditor.addListener("next-default", () => this.parentWidget?.nextDefault.call(this.parentWidget, this));

        const result = this.appendWidget(this.#textEditor);

        if (editable) {
            this.appendEditGui();
        }
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WListItem}
     */
    static default(parent, editable = false) {
        return new WListItem({
            text: []
        }, parent, editable);
    }

    /**
     * @override
     * @param {ListItemJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WListItem}
     */
    static build(json, parent, editable = false) {
        return new WListItem(json, parent, editable);
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
            type: "WListItem",
            text: this.#textEditor.save().content
        };
    }

    saveCompact() {
        return this.#textEditor.save().content;
    }

    focus() {
        this.children[0].focus();
    }
}



widgets.define("WListItem", WListItem);
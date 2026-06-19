class WText extends Widget {
    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef TextJSONType
     * @property {TextEditorJSON} textEditor
     *
     * @typedef {TextJSONType & WidgetJSON} TextJSON
     */


    #textEditor;

    /**
     * @param {TextJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(jsml.p("w-text"), parent, editable);
        this.removeInspectHandler();
        json.textEditor.mode = "simple";
        this.#textEditor = WTextEditor.build(json.textEditor, this, editable);
        // this.#textEditor.setMode("fancy");
        this.childSupport = 1;
        this.appendWidget(this.#textEditor);

        if (editable !== true) {
            return;
        }

        this.appendEditGui();
        this.rootElement.classList.add("edit");
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WText}
     */
    static default(parent, editable) {
        return WText.build({
            textEditor: {
                content: [],
                mode: "fancy"
            }
        }, parent, editable);
    }

    /**
     * @override
     * @param {TextJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WText}
     */
    static build(json, parent, editable = false) {
        return new WText(json, parent, editable);
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
     * @returns {TextJSON}
     */
    save() {
        return {
            type: "WText",
            textEditor: this.#textEditor.save()
        };
    }


    focus() {
        this.#textEditor.focus();
    }
}



widgets.define("WText", WText);
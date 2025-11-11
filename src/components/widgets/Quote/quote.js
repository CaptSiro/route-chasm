class WQuote extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef QuoteJSONType
     * @property {TextEditorJSONContent=} text
     * @property {TextEditorJSONContent=} author
     *
     * @typedef {QuoteJSONType & WidgetJSON} QuoteJSON
     */

    #textEditor;
    #authorEditor;

    /**
     * @param {QuoteJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(jsml.div("w-quote", [
            jsml.span("decorative", ",,")
        ]), parent, editable);
        this.childSupport = this.childSupport;

        this.#textEditor = WTextEditor.build({
            content: json.text ?? [],
            mode: "simple",
            forceSingleLine: false,
            hint: "Life’s good, you should get one."
        }, this, editable);

        this.#authorEditor = WTextEditor.build({
            content: json.author ?? [],
            mode: "simple",
            forceSingleLine: true,
            hint: "Unnamed author"
        }, this, editable);

        this.rootElement.append(
            jsml.div("text", [
                this.#textEditor.rootElement
            ]),
            jsml.div("author", [
                jsml.span("decorative", "-"),
                this.#authorEditor.rootElement
            ])
        );

        if (editable) {
            this.appendEditGui();
        }
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WQuote}
     */
    static default(parent, editable = false) {
        return new WQuote({}, parent, editable);
    }

    /**
     * @override
     * @param {QuoteJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WQuote}
     */
    static build(json, parent, editable = false) {
        return new WQuote(json, parent, editable);
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
            type: "WQuote",
            text: this.#textEditor.save().content,
            author: this.#authorEditor.save().content
        };
    }
}



widgets.define("WQuote", WQuote);
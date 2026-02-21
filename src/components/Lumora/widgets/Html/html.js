class WHtml extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef HtmlJSONType
     * @property {string=} html
     *
     * @typedef {HtmlJSONType & WidgetJSON} HtmlJSON
     */

    #htmlEditor;
    #preview;

    /**
     * @param {HtmlJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        const code = json.html ?? WHtml.defaultHtml();
        const preview = jsml.div("w-html-preview");
        preview.innerHTML = code;

        super(jsml.div("w-html", [preview]), parent, editable);
        this.childSupport = 1;

        this.#preview = preview;

        if (editable !== true) {
            return;
        }

        this.appendEditGui();
        this.rootElement.classList.add("edit");

        const htmlEditor = jsml.pre({ contenteditable: true }, code);
        this.#htmlEditor = htmlEditor;

        this.rootElement.prepend(jsml.div("w-html-editor display-none", htmlEditor));

        this.rootElement.prepend(jsml.button(
            {
                class: 'w-html-toggle',
                onClick: () => {
                    preview.innerHTML = htmlEditor.textContent;
                    preview.classList.toggle('display-none');
                    htmlEditor.parentElement.classList.toggle('display-none');
                }
            },
            Icon('nf-fa-edit', 'Edit')
        ));
    }

    extractPreContent(pre) {
        const extractNode = node => {
            if (node.nodeType === Node.TEXT_NODE) {
                return node.nodeValue;
            }

            if (node.nodeType !== Node.ELEMENT_NODE) {
                return '';
            }

            const tag = node.tagName;
            if (tag === 'BR') {
                return '\n';
            }

            const isBlock =
                tag === 'DIV' ||
                tag === 'P' ||
                tag === 'LI';

            let ret = '';
            if (isBlock && result.length > 0 && !result.endsWith('\n')) {
                ret += '\n';
            }

            for (let child of node.childNodes) {
                ret += extractNode(child);
            }

            if (isBlock && !result.endsWith('\n')) {
                ret += '\n';
            }

            return ret;
        }

        let result = '';

        for (let child of pre.childNodes) {
            result += extractNode(child);
        }

        return result;
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WHtml}
     */
    static default(parent, editable = false) {
        return new WHtml({
            html: WHtml.defaultHtml()
        }, parent, editable);
    }

    static defaultHtml() {
        return '<h3>HTML Widget</h3>';
    }

    /**
     * @override
     * @param {HtmlJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WHtml}
     */
    static build(json, parent, editable = false) {
        return new WHtml(json, parent, editable);
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
        const html = this.extractPreContent(this.#htmlEditor);

        return {
            type: "WHtml",
            html
        };
    }
}



widgets.define("WHtml", WHtml);
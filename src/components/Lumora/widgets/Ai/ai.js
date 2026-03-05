class WAi extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef AiJSONType
     * @property {string} prompt
     * @property {string | undefined} html
     * @property {string | undefined} css
     * @property {string | undefined} js
     *
     * @typedef {AiJSONType & WidgetJSON} AiJSON
     */

    #textEditor;
    /** @type {HTMLElement} */
    #promptArea;
    /**
     * @type {Observable<DividerJSON>}
     */
    #json;
    #id;
    #iframe;

    /**
     * @param {AiJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(jsml.div("w-ai"), parent, editable);

        this.rootElement.id = this.#id = std_id_html(8);
        this.childSupport = 0;

        this.#json = new Observable(json);
        this.mount();

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
     * @returns {WAi}
     */
    static default(parent, editable = false) {
        return new WAi({
            prompt: "",
        }, parent, editable);
    }

    /**
     * @override
     * @param {AiJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WAi}
     */
    static build(json, parent, editable = false) {
        return new WAi(json, parent, editable);
    }

    mount() {
        const { html, css, js } = this.#json.value;

        if (!is(html) || !is(css) || !is(js)) {
            return;
        }

        this.rootElement.innerHTML = html;

        const id = "#" + this.#id + "_style";
        $(id)?.remove();

        document.head.append(jsml.style({ id }, css));

        const exec = new Function("container", js);
        std_dom_onMount("#" + this.#id).then(() => {
            exec(this.rootElement);
        });
    }

    focus() {
        editor_inspect(this.inspectorHTML, this);
    }

    /**
     * @override
     * @returns {Content}
     */
    get inspectorHTML() {
        this.#promptArea ??= TextAreaInspector(
            String(this.#json.value.prompt ?? ""),
            async (value, parentElement) => {
                this.#json.value.prompt = value;
                return true;
            },
            "Prompt",
            "Create table..."
        );

        $("textarea", this.#promptArea)
            ?.setAttribute("rows", "5");
        
        return [
            TitleInspector("Ai component"),

            HRInspector(),

            this.#promptArea,
            jsml.div(_, jsml.button({
                onClick: async () => {
                    const w = window_create(
                        "",
                        jsml.div("text-window", [
                            jsml.h3(_, "Generating widget..."),
                        ]),
                        {
                            isDialog: true
                        }
                    );

                    window_open(w);
                    const response = await fetch(window.location, {
                        method: "GENERATE",
                        body: JSON.stringify({
                            prompt: this.#json.value.prompt
                        })
                    });

                    if (await std_fetch_handleServerError(response)) {
                        return;
                    }

                    const json = await response.json();
                    window_close(w);

                    if (!response.ok) {
                        await window_alert(json.message);
                        return;
                    }

                    this.#json.value.html = json.html;
                    this.#json.value.css = json.css;
                    this.#json.value.js = json.js;
                    this.mount();
                }
            }, "Generate"))
        ];
    }

    /**
     * @override
     * @returns {AiJSON}
     */
    save() {
        return {
            type: "WAi",
            prompt: this.#json.value.prompt,
            html: this.#json.value.html,
            css: this.#json.value.css,
            js: this.#json.value.js,
        };
    }
}



window.addEventListener("message", event => {
    console.log("message events");

    if (!is(event.data?.type)) {
        return;
    }

    if (event.data.type === "hover") {
        const widgetContainer = $("#" + event.data.id);
        widgetContainer.widget?.select();
    }

    if (event.data.type === "click") {
        const widgetContainer = $("#" + event.data.id);
        widgetContainer.widget?.focus();
    }
});

widgets.define("WAi", WAi);
class WDivider extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef DividerJSONType
     * @property {boolean=} doShowFilling Default true
     * @property {"start" | "center" | "end"=} fillPosition
     * @property {number=} dividerAmount
     *
     * @typedef {DividerJSONType & WidgetJSON} DividerJSON
     */

    #fillingElement;
    /**
     * @type {Observable<DividerJSON>}
     */
    #json;
    #resizeable;
    #container;

    static MAX_AMOUNT = 4096;
    static MIN_AMOUNT = 16;

    /**
     * @param {DividerJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(jsml.div(
            "w-divider w-divider-container" + (json?.doShowFilling === true
                ? " doShowFilling"
                : "")
        ), parent, editable);
        this.childSupport = this.childSupport;

        json.dividerAmount ||= 64;
        this.#container = this.rootElement;

        this.#fillingElement = jsml.div("filling");
        this.rootElement.appendChild(this.#fillingElement);

        if (json.dividerAmount !== undefined) {
            this.rootElement.style.setProperty("--amount", json.dividerAmount + "px");
        }

        if (json.fillPosition !== undefined) {
            this.rootElement.style.setProperty("--fill-position", json.fillPosition);
        }

        if (editable) {
            this.#json = new Observable(json);
            this.#json.onChange(descriptor => {
                this.rootElement.style.height = descriptor.dividerAmount + "px";
            });

            this.#resizeable = new Resizeable(this.rootElement, { axes: "vertical" });
            this.#resizeable.on("resize", (width, height) => {
                this.#json.setProperty("dividerAmount", std_clamp(WDivider.MIN_AMOUNT, WDivider.MAX_AMOUNT, height));
            });

            this.#resizeable.content.classList.add("w-divider-container");
            this.#container = this.#resizeable.content;
            this.rootElement.classList.remove("w-divider-container");

            this.appendEditGui();
        }
    }

    focus() {
        editor_inspect(this.inspectorHTML, this);
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WDivider}
     */
    static default(parent, editable = false) {
        return new WDivider({
            doShowFilling: true
        }, parent, editable);
    }

    /**
     * @override
     * @param {DividerJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WDivider}
     */
    static build(json, parent, editable = false) {
        return new WDivider(json, parent, editable);
    }

    #dividerHeightField;
    #fillPositionRadioGroup;

    /**
     * @override
     * @returns {Content}
     */
    get inspectorHTML() {
        if (this.#dividerHeightField === undefined || this.#fillPositionRadioGroup === undefined) {
            this.#json.onChange(descriptor => {
                this.#dividerHeightField.querySelector("input").value = String(Math.round(descriptor.dividerAmount));
                this.#fillPositionRadioGroup.classList.toggle("display-none", !descriptor.doShowFilling);
                this.rootElement.style.setProperty("--fill-position", descriptor.fillPosition ?? "center");
            });
        }

        this.#dividerHeightField ||= TextFieldInspector(
            String(Math.round(this.#json.value.dividerAmount)),
            (value, parentElement) => {
                const number = +value;
                if (isNaN(number)) {
                    return false;
                }

                this.#json.setProperty("dividerAmount", std_clamp(WDivider.MIN_AMOUNT, WDivider.MAX_AMOUNT, number));

                std_dom_validated(parentElement);
                return true;
            },
            "Height",
            "32"
        );

        this.#fillPositionRadioGroup ||= RadioGroupInspector((value, parentElement) => {
            this.#json.setProperty("fillPosition", value);
            std_dom_validated(parentElement);
            return true;
        }, selectOption([
            { text: "Top", value: "start" },
            { text: "Center", value: "center" },
            { text: "Bottom", value: "end" }
        ], this.#json.value.fillPosition, "center"), "Line position");
        this.#fillPositionRadioGroup.classList.toggle("display-none", !this.#json.value.doShowFilling);


        this.#json.dispatch();


        return [
            TitleInspector("Divider"),

            HRInspector(),

            TitleInspector("Properties"),
            this.#dividerHeightField,
            CheckboxInspector(this.#json.value.doShowFilling ?? true, value => {
                this.#json.setProperty("doShowFilling", value);
                this.rootElement.classList.toggle("doShowFilling", value);
                return true;
            }, "Show line"),
            this.#fillPositionRadioGroup
        ];
    }

    /**
     * @override
     * @returns {WidgetJSON}
     */
    save() {
        return {
            type: "WDivider",
            dividerAmount: this.#json.value.dividerAmount,
            doShowFilling: this.#json.value.doShowFilling,
            fillPosition: this.#json.value.fillPosition
        };
    }
}



widgets.define("WDivider", WDivider);
const { div, span, label, input, h3 } = jsml;


/**
 * @template S
 * @callback Setter
 * @param {S} value
 * @param {HTMLElement} parentElement
 * @returns {boolean | Promise<boolean>}
 */

/**
 * @param {Event} event
 * @param {Setter<string>} setter
 * @param {string | undefined} lastValue
 * @param {HTMLElement} parent
 * @param {HTMLCollection} collection
 * @returns {(function(*): (*))|*}
 */
async function choiceChangeListener(event, setter, lastValue, parent, collection) {
    if (await setter(event.target.value, parent)) {
        return event.target.value;
    }

    if (collection.length === 0) {
        return lastValue;
    }

    const attribute = collection[0].tagName === "OPTION"
        ? "selected"
        : "checked"

    for (const element of collection) {
        if (element.value !== lastValue) {
            element[attribute] = false;
            continue;
        }

        element[attribute] = true;
    }

    return lastValue;
}

/**
 * @param {boolean} state
 * @param {Setter<boolean>} setter
 * @param {string} label
 * @returns {HTMLElement}
 */
function CheckboxInspector(state, setter, label = "") {
    const checkbox = (
        CheckBox(state, label, {
            onChange: async event => {
                if (!(await setter(event.target.checked, checkbox))) {
                    event.target.checked = !event.target.checked;
                }
            }
        })
    );

    checkbox.classList.add("i-checkbox");
    return checkbox;
}

/**
 * @typedef KeyValuePair
 * @property {string} text
 * @property {string} value
 * @property {boolean=} selected
 */
/**
 * @param {Setter<string>} setter
 * @param {KeyValuePair[]} radios
 * @param {string} label
 * @returns {HTMLElement}
 */
function RadioGroupInspector(setter, radios, label = undefined) {
    const name = std_id_html(8);
    let lastValue = radios
        .reduce(
            (last, current) => current.selected ? current.value : last,
            undefined
        );

    const radioGroup = (
        div({
            class: "i-radio-group",
            onChange: async event => {
                lastValue = await choiceChangeListener(
                    event,
                    setter,
                    lastValue,
                    radioGroup,
                    radioGroup.querySelectorAll(`input[name=${name}]`)
                );
            }
        }, [
            Optional(label !== undefined,
                span(_, label)
            ),
            ...radios.map(radio => {
                return (
                    Radio(radio.text, radio.value, name, _,
                        radio.selected !== undefined
                            ? { checked: radio.selected }
                            : undefined
                    )
                );
            })
        ], )
    );

    return radioGroup;
}

/**
 * @param {string | undefined} state
 * @param {Setter<string>} setter
 * @param {string} label
 * @param {string} placeholder
 * @param {Props} props
 * @returns {HTMLElement}
 */
function TextAreaInspector(state, setter, label = undefined, placeholder = undefined, props = {}) {
    const container = div("i-text-area");

    props.onBlur ??= async event => {
        if (state === event.target.value) {
            return;
        }

        if (await setter(event.target.value, parent)) {
            state = event.target.value;
            return;
        }

        event.target.value = state ?? "";
    };

    props.onKeydown ??= event => {
        if (!(event.key === "Enter" && event.ctrlKey)) {
            return;
        }

        event.target.dispatchEvent(new Event("blur"));
    }

    if (is(state)) {
        props.value = state;
    }

    if (is(placeholder)) {
        props.placeholder = placeholder;
    }

    const area = jsml.textarea(props, state);
    const labelElement = LabelFactory(label, area);

    if (placeholder) {
        area.setAttribute("placeholder", placeholder);
    }

    container.append(
        labelElement,
        area
    );

    return container;
}

/**
 * @param {string | undefined} state
 * @param {Setter<string>} setter
 * @param {string} label
 * @param {string} placeholder
 * @param {Props} props
 * @returns {HTMLElement}
 */
function TextFieldInspector(state, setter, label = undefined, placeholder = undefined, props = {}) {
    const container = div("i-text-field");

    props.onBlur ??= async event => {
        if (state === event.target.value) {
            return;
        }

        if (await setter(event.target.value, container)) {
            state = event.target.value;
            return;
        }

        event.target.value = state ?? "";
    };

    props.onKeydown ??= event => {
        if (event.key !== "Enter") return;
        event.target.dispatchEvent(new Event("blur"));
    };

    if (is(state)) {
        props.value = state;
    }

    if (is(placeholder)) {
        props.placeholder = placeholder;
    }

    props.type = "text";
    const textField = jsml.input(props);
    const labelElement = LabelFactory(label, textField);

    container.append(
        labelElement,
        textField
    );

    return container;
}

/**
 * @param {number | undefined} state
 * @param {Setter<string | number>} setter
 * @param {string} label
 * @param {string} placeholder
 * @param {Content} measurement
 * @param {Props} props
 * @returns {HTMLElement}
 */
function NumberInspector(state, setter, label = undefined, placeholder = undefined, measurement = undefined, props = {}) {
    const id = std_id_html(8);
    props.id = id;

    if (is(placeholder)) {
        props.placeholder = placeholder;
    }

    if (is(placeholder)) {
        props.placeholder = placeholder;
    }

    if (is(state)) {
        props.value = state;
    }

    props.type = "number";

    return (
        div("i-number", [
            Optional(is(label), jsml.label({ for: id }, label)),
            jsml.input(props),
            typeof measurement === "string"
                ? span(_, measurement)
                : measurement
        ])
    );
}

//TODO: create custom date picker
/**
 * @param {Date | undefined} state
 * @param {Setter<Date>} setter
 * @param {string} label
 * @param {boolean} isDateTime
 * @param {Props} props
 * @returns {HTMLElement}
 */
function DateInspector(state, setter, label = undefined, isDateTime = false, props = {}) {
    const input = jsml.input({
        type: isDateTime ? "datetime-local" : "date"
    });
    input.addEventListener("change", async () => {
        const date = new Date(input.value);
        if (state === date) {
            return;
        }

        if (await setter(date, input.parentElement)) {
            state = date;
            return;
        }

        input.value = state?.toISOString().slice(0, 16);
    });

    if (is(state)) {
        const timezoneOffset = new Date().getTimezoneOffset() * 60 * 1000;
        const localDateTime = new Date(state.getTime() - timezoneOffset);
        input.value = localDateTime.toISOString().slice(0, 16);
    }

    return (
        LabelAndComponentInspector("i-date", label, input)
    );
}

/**
 * @param {Setter<string>} setter
 * @param {KeyValuePair[]} options
 * @param {string} label
 * @param {string} className
 * @returns {HTMLElement}
 */
function SelectInspector(setter, options, label = undefined, className = undefined) {
    const id = std_id_html(8);
    let lastValue = options
        .reduce(
            (last, current) => current.selected ? current.value : last,
            undefined
        );

    const container = div("i-select dont-force" + (className !== undefined ? (" " + className) : ""));
    const select = (
        jsml.select(
            {
                onChange: async event => {
                    lastValue = await choiceChangeListener(event, setter, lastValue, container, select.children);
                }
            },
            options.map(option =>
                new Option(option.text, option.value, _, option?.selected)
            ),
        )
    );

    jsml_addContent(container, [
        Optional(is(label), jsml.label({ for: id }, label)),
        div("select-container", select)
    ]);

    return container;
}

/**
 * @param {string | undefined} state
 * @param {Setter<string>} setter
 * @param {string} label
 * @param {string} placeholder
 * @param {Props} props
 * @returns {HTMLElement}
 */
function ColorPickerInspector(state, setter, label = undefined, placeholder = undefined, props = {}) {
    if (is(state)) {
        props.value = state;
    }

    props.type = "color";
    const input = jsml.input(props);
    const container = LabelAndComponentInspector("i-date", label, input, placeholder);

    input.addEventListener("change", async event => {
        if (state === event.target.value) {
            return;
        }

        await setter(event.target.value, container);
    });

    return container;
}

/**
 * @param {string} title
 * @param {string} className
 * @returns {HTMLElement}
 */
function TitleInspector(title, className = undefined) {
    return (
        h3("i-title" + (className !== undefined ? " " + className : ""), title)
    );
}

function NoteInspector(note, className = undefined) {
    return (
        jsml.h5("i-note" + (className !== undefined ? " " + className : ""), note)
    );
}

function HRInspector(className = undefined) {
    return (
        div("i-hr" + (className !== undefined ? " " + className : ""), "​") //todo does not display without zero-width-character (?)
    );
}

/**
 * @param {KeyValuePair[]} options
 * @param {string} value
 * @param {string} defaultValue
 * @return {KeyValuePair[]}
 */
function selectOption(options, value, defaultValue = undefined) {
    let defaultOption;
    for (const option of options) {
        if (option.value === defaultValue) {
            defaultOption = option;
        }
        if (option.value !== value) continue;

        option.selected = true;
        return options;
    }

    defaultOption.selected = true;
    return options;
}

/**
 * @template T
 * @param {string} className
 * @param {string} label
 * @param {HTMLElement} component
 * @param {string | undefined} placeholder
 * @returns {HTMLElement}
 */
function LabelAndComponentInspector(className, label, component, placeholder = undefined) {
    const id = std_id_html(8);

    component.id = id;
    if (placeholder) {
        component.setAttribute("placeholder", placeholder);
    }

    return (
        div(className, [
            Optional(label !== undefined,
                jsml.label({ for: id }, label)
            ),
            component
        ])
    );
}

function NotInspectorAble() {
    return undefined;
}



class ColorPicker {
    #rootElement;
    /**
     * @return {HTMLElement}
     */
    get rootElement() {
        return this.#rootElement;
    }

    #display;
    #red;
    #green;
    #blue;
    #alpha;
    #old;
    #new;

    constructor(inline = false, cancelAction = () => {}, pickAction = () => {}) {
        this.#rootElement = ColorPicker.createColorPicker(inline);

        this.#red = this.#rootElement.querySelector(".color-picker-r");
        this.#red.addEventListener("input", this.onRgbChange("red"));

        this.#green = this.#rootElement.querySelector(".color-picker-g");
        this.#green.addEventListener("input", this.onRgbChange("green"));

        this.#blue = this.#rootElement.querySelector(".color-picker-b");
        this.#blue.addEventListener("input", this.onRgbChange("blue"));

        this.#alpha = this.#rootElement.querySelector(".color-picker-a");
        this.#alpha.addEventListener("input", this.onRgbChange("alpha"));

        this.#display = this.#rootElement.querySelector(".format");
        this.#display.addEventListener("input", event => {
            const color = this.parseColor(event.target.value);
            if (color === null) {
                this.#display.classList.add("invalid");
                return;
            }

            this.#display.classList.remove("invalid");
            this.setNewColor(color);
        });

        this.#old = this.#rootElement.querySelector(".old");
        this.#new = this.#rootElement.querySelector(".new");

        this.#rootElement.querySelector(".cancel")?.addEventListener("click", cancelAction);
        this.#rootElement.querySelector(".pick")?.addEventListener("click", pickAction);
    }

    static createColorPicker(inline = false) {
        const guids = Array(5).fill(null).map(() => std_id_html(8));

        return (
            div("color-picker", [
                Optional(inline === false,
                    div("showcase", [
                        div("transparent"),
                        div("old"),
                        div("new")
                    ])
                ),
                div("sliders", [
                    div("row", [
                        jsml.label({ for: guids[0] }, "R:"),
                        jsml.input({
                            class: "color-picker-r",
                            type: "range",
                            min: "0",
                            max: "255",
                            value: "0",
                            id: guids[0]
                        })
                    ]),
                    div("row", [
                        jsml.label({ for: guids[1] }, "G:"),
                        jsml.input({
                            class: "color-picker-g",
                            type: "range",
                            min: "0",
                            max: "255",
                            value: "0",
                            id: guids[1]
                        })
                    ]),
                    div("row", [
                        jsml.label({ for: guids[2] }, "B:"),
                        jsml.input({
                            class: "color-picker-b",
                            type: "range",
                            min: "0",
                            max: "255",
                            value: "0",
                            id: guids[2]
                        })
                    ]),
                    div("row", [
                        jsml.label({ for: guids[3] }, "A:"),
                        jsml.input({
                            class: "color-picker-a",
                            type: "range",
                            min: "0",
                            max: "1",
                            step: "0.01",
                            value: "0",
                            id: guids[3]
                        })
                    ])
                ]),
                div("row", [
                    jsml.label({ class: "format-label", for: guids[4] }),
                    jsml.input({ class: "format", type: "text", id: guids[4] })
                ]),
                Optional(inline === false,
                    div("controls", [
                        jsml.button("button-like-main cancel", "Cancel"),
                        jsml.button("button-like-main pick", "Pick")
                    ])
                )
            ])
        );
    }

    /**
     * @param {Color} color
     */
    static toHex(color) {
        return "#" + [
            color.red.toString(16),
            color.green.toString(16),
            color.blue.toString(16),
            Math.round(color.alpha * 255).toString(16)
        ]
            .map(channel => (channel.length === 1 ? ("0" + channel) : channel))
            .join("");
    }

    setChannel(channel, value) {
        this.#rootElement.style.setProperty("--" + channel, value);
    }

    onRgbChange(channel) {
        return event => {
            this.setChannel(channel, event.target.value);
            this.displayCurrentColor();
            this.#rootElement.dispatchEvent(new CustomEvent("pick", { detail: this.getCurrentColor() }));
        };
    }

    /**
     * @typedef Color
     * @property {number} red
     * @property {number} green
     * @property {number} blue
     * @property {number} alpha
     */
    /**
     * @param {string} colorFormat
     * @return {Color | null}
     */
    parseColor(colorFormat) {
        let values = /^rgba?\((25[0-5]|2[0-4][0-9]|1?[0-9]{1,2}) ?, ?(25[0-5]|2[0-4][0-9]|1?[0-9]{1,2}) ?, ?(25[0-5]|2[0-4][0-9]|1?[0-9]{1,2}) ?,? ?(1|0|0\.[0-9]+)?\)$/.exec(colorFormat);
        if (values !== null) {
            return {
                red: +values[1],
                green: +values[2],
                blue: +values[3],
                alpha: +(values[4] ?? 1)
            };
        }

        switch (colorFormat.length) {
            case 4:
                values = /^#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])$/.exec(colorFormat);
                if (values === null) return null;
                return {
                    red: parseInt(values[1].repeat(2), 16),
                    green: parseInt(values[2].repeat(2), 16),
                    blue: parseInt(values[3].repeat(2), 16),
                    alpha: 1
                };
            case 7:
                values = /^#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/.exec(colorFormat);
                if (values === null) return null;
                return {
                    red: parseInt(values[1], 16),
                    green: parseInt(values[2], 16),
                    blue: parseInt(values[3], 16),
                    alpha: 1
                };
            case 9:
                values = /^#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/.exec(colorFormat);
                if (values === null) return null;
                return {
                    red: parseInt(values[1], 16),
                    green: parseInt(values[2], 16),
                    blue: parseInt(values[3], 16),
                    alpha: parseInt(values[4], 16) / 255
                };
            default:
                return null;
        }
    }

    setOldColor(colorFormat) {
        const color = this.parseColor(colorFormat);
        if (color === null) {
            throw "Unknown color format. Currently supported: RGB, RGBA, HEX(3 letters), HEX (6 letters), HEX + alpha (8 letters)";
        }

        this.#old.style.backgroundColor = `rgba(${color.red}, ${color.green}, ${color.blue}, ${color.alpha})`;
        this.setNewColor(color);
        this.displayCurrentColor();
    }

    setNewFromFormat(colorFormat) {
        const color = this.parseColor(colorFormat);
        if (color === null) {
            throw "Unknown color format. Currently supported: RGB, RGBA, HEX(3 letters), HEX (6 letters), HEX + alpha (8 letters)";
        }

        this.setNewColor(color);
        this.displayCurrentColor();
    }

    /**
     * @param {Color} color
     */
    setNewColor(color) {
        this.#red.value = color.red;
        this.setChannel("red", color.red);

        this.#green.value = color.green;
        this.setChannel("green", color.green);

        this.#blue.value = color.blue;
        this.setChannel("blue", color.blue);

        this.#alpha.value = color.alpha;
        this.setChannel("alpha", color.alpha);

        this.#rootElement.dispatchEvent(new CustomEvent("pick", { detail: color }));
    }

    getCurrentColor() {
        return {
            red: Number(this.#rootElement.style.getPropertyValue("--red")),
            green: Number(this.#rootElement.style.getPropertyValue("--green")),
            blue: Number(this.#rootElement.style.getPropertyValue("--blue")),
            alpha: Number(this.#rootElement.style.getPropertyValue("--alpha"))
        };
    }

    displayCurrentColor(color = undefined) {
        this.#display.value = ColorPicker.toHex(color ?? this.getCurrentColor());
    }
}
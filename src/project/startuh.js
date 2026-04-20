const STARTUH_ANIMATION_DURATION = 500;
const STARTUH_KEY_LAYOUT = "startuh_layout";
const STARTUH_KEY_EDIT_MODE = "startuh_edit-mode";
const STARTUH_KEY_WIDGETS = "startuh_widgets";

const startuh_editMode = new Impulse({ default: false });
const startuh_content = $(".layers > .content");
const startuh_inspector = $(".inspector-container > .inspector");
const startuh_widgets_element = $(".widgets > .container");
/** @type {Map<HTMLElement, StartuhWidget>} */
const startuh_widgets = new Map();
/** @type {Map<string, StartuhBuilder>} */
const startuh_builders = new Map();
const startuh_main = $('main');
const startuh_layout = JSON.parse(localStorage.getItem(STARTUH_KEY_LAYOUT) ?? "[300, 300]");

let startuh_animateTimeout = null;
/** @type {HTMLElement | null} */
let startuh_currentWidget = null;



startuh_updateLayout();
if (Boolean(localStorage.getItem(STARTUH_KEY_EDIT_MODE))) {
    startuh_editToggle();
}



function startuh_load() {
    const widgets = JSON.parse(localStorage.getItem(STARTUH_KEY_WIDGETS) ?? "[]");

    for (let i = 0; i < widgets.length; i++) {
        /** @type {StartuhWidgetConfig} */
        const widget = widgets[i];

        const builder = startuh_builders.get(widget.builder);
        if (!is(builder)) {
            continue;
        }

        startuh_addWidget(builder.build(widget));
    }
}

window.addEventListener("load", startuh_load, { once: true });

function startuh_save() {
    const configs = [];

    for (const widget of startuh_widgets.values()) {
        configs.push(widget.save());
    }

    localStorage.setItem(
        STARTUH_KEY_WIDGETS,
        JSON.stringify(configs)
    );
}

function startuh_editToggle() {
    startuh_editMode.pulse(!startuh_editMode.value());

    localStorage.setItem(STARTUH_KEY_EDIT_MODE, String(startuh_editMode.value()));
    startuh_main.classList.toggle('edit', startuh_editMode.value());

    startuh_main.classList.add("animate");
    if (is(startuh_animateTimeout)) {
        clearTimeout(startuh_animateTimeout);
    }

    startuh_animateTimeout = setTimeout(() => {
        startuh_main.classList.remove("animate");
        startuh_animateTimeout = null;
    }, STARTUH_ANIMATION_DURATION);
}

/**
 * @param {StartuhBuilder} builder
 */
function startuh_addBuilder(builder) {
    startuh_builders.set(builder.name, builder);
}

function startuh_addWidget(widget) {
    const element = widget.instantiate();
    startuh_widgets.set(element, widget);

    startuh_addContent(element);
}

function startuh_addContent(element) {
    startuh_content.append(element);
}

startuh_content.addEventListener("pointerdown", event => {
    const widget = event.target.classList.contains('widget')
        ? event.target
        : event.target.closest(".widget");

    if (!is(widget)) {
        startuh_currentWidget?.classList.remove("focus");
        startuh_currentWidget = null;
        return;
    }

    if (widget.classList.contains("focus")) {
        return;
    }

    startuh_currentWidget?.classList.remove("focus");
    widget.classList.add("focus");
    startuh_currentWidget = widget;

});

window.addEventListener("keydown", event => {
    if (!is(startuh_currentWidget)) {
        return;
    }

    if (event.altKey || event.ctrlKey || event.shiftKey) {
        return;
    }

    if (event.key !== "Delete" && event.key !== "Backspace") {
        return;
    }

    startuh_currentWidget.remove();
    startuh_widgets.delete(startuh_currentWidget);
    startuh_currentWidget = null;
    startuh_save();
});

function startuh_addPrefab(element) {
    startuh_widgets_element.append(element);
}

/**
 * @param {Content} content
 */
function startuh_inspect(content) {
    if (!is(content) || (Array.isArray(content) && content.length === 0)) {
        return;
    }

    startuh_inspector.innerHTML = "";
    jsml_addContent(startuh_inspector, content);
}

function startuh_updateLayout() {
    startuh_main.style.setProperty("--column-0", startuh_layout[0] + "px");
    startuh_main.style.setProperty("--column-1", startuh_layout[1] + "px");
    localStorage.setItem(STARTUH_KEY_LAYOUT, JSON.stringify(startuh_layout));
}



/**
 * @template T
 * @implements {StartuhBuilder<T, StartuhWidgetConfig>}
 */
class FunctionalStartuhBuilder {
    /** @type {(config?: StartuhWidgetConfig) => T} */
    #builder;

    /** @type {string} */
    #name;



    /**
     * @param {string} name
     * @param {(config?: StartuhWidgetConfig) => StartuhWidget} builder
     */
    constructor(name, builder) {
        this.#name = name;
        this.#builder = builder;
    }



    build(config) {
        return this.#builder(config);
    }

    create() {
        return this.#builder({
            builder: this.#name,
            x: 0.5,
            y: 0.5
        });
    }

    get name() {
        return this.#name;
    }
}

class StartuhWidget {
    /** @type {Vec2} */
    position;

    setPosition(x, y) {
        this.position = new Vec2(x, y);
        startuh_save();
    }

    instantiate() {
        return startuh_WidgetElement(this, "Widget");
    }

    /**
     * @return {Content}
     */
    inspect() {
        return [];
    }

    /**
     * @returns {StartuhWidgetConfig}
     */
    save() {
        return {
            builder: "",
            x: this.position.x,
            y: this.position.y,
        }
    }
}



/**
 * @param {StartuhWidget} context
 * @param {Content} content
 * @param {number | undefined} x
 * @param {number | undefined} y
 */
function startuh_WidgetElement(context, content, { x, y } = {}) {
    const percentage = (a, b) => ((a / b) * 100) + "%";
    const coords = c => is(c) ? (c * 100) + "%" : "50%";

    let moving = false;
    let mouseOffset = std_vec2(0, 0);

    let positionX = x ?? 0.5;
    let positionY = y ?? 0.5;

    context.setPosition(positionX, positionY);

    const widget = jsml.div({
        class: "widget glass",

        style: {
            left: coords(x),
            top: coords(y)
        },

        /** @param {PointerEvent} event */
        onPointerDown: event => {
            if (!startuh_editMode.value()) {
                return;
            }

            const that = widget.getBoundingClientRect();
            mouseOffset = std_vec2(event.x - that.x, event.y - that.y);

            widget.setPointerCapture(event.pointerId);
            moving = true;

            startuh_inspect(context.inspect());
        },

        /** @param {PointerEvent} event */
        onPointerMove: event => {
            if (!moving) {
                return;
            }

            const that = widget.getBoundingClientRect();
            const container = widget.parentElement.getBoundingClientRect();

            const x = event.x - mouseOffset.x;
            const y = event.y - mouseOffset.y;

            const widgetX = std_clamp(0, container.width - that.width, x - container.x);
            const widgetY = std_clamp(0, container.height - that.height, y - container.y);

            positionX = widgetX / container.width;
            positionY = widgetY / container.height;

            widget.style.left = percentage(widgetX, container.width);
            widget.style.top = percentage(widgetY, container.height);
        },

        /** @param {PointerEvent} event */
        onPointerUp: event => {
            context.setPosition(positionX, positionY);
            widget.releasePointerCapture(event.pointerId);
            moving = false;
        }
    }, content);

    return widget;
}

/**
 * @param {StartuhBuilder<StartuhWidget, any>} builder
 * @param {HTMLElement} icon
 * @param {string} name
 */
function startuh_PrefabElement(builder, icon, name) {
    return jsml.div({
        class: "row prefab",
        onClick: () => {
            startuh_addWidget(builder.create());
        }
    }, [
        icon,
        jsml.span(_, name)
    ]);
}

/**
 * @param {HTMLElement} element
 * @param {string} index
 * @param {string} sign
 * @param {string} range
 */
function startuh_resizeHandle(element, { index, sign, range }) {
    sign ??= "1";
    if (!is(index)) {
        return;
    }

    const interval = std_range(range);
    const multiplier = Math.sign(Number(sign));
    const i = Number(index);
    let moving = false;

    element.addEventListener("pointerdown", event => {
        element.setPointerCapture(event.pointerId);
        moving = true;
    });

    element.addEventListener("pointermove", event => {
        if (!moving) {
            return;
        }

        startuh_layout[i] = interval.clamp(startuh_layout[i] + multiplier * event.movementX);
        startuh_updateLayout();
    });

    element.addEventListener("pointerup", event => {
        element.releasePointerCapture(event.pointerId);
        moving = false;
    });
}

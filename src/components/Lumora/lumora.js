let lumora_viewport_resizeAnimationDuration = 250;



/** @type {LumoraViewportMode} */
const LUMORA_VIEWPORT_MODE_COMPUTER = {
    name: 'computer',
    aspectRatio: 16 / 9
};

/** @type {LumoraViewportMode} */
const LUMORA_VIEWPORT_MODE_MOBILE = {
    name: 'mobile',
    aspectRatio: 9 / 16
};

/** @type {LumoraViewportResizeListener[]} */
const lumora_viewport_listeners = [];
/** @type {LumoraViewportDimension} */
let lumora_viewport_dimension;
/** @type {LumoraViewportMode} */
let lumora_viewport_mode = LUMORA_VIEWPORT_MODE_COMPUTER;
let lumora_viewport, lumora_viewport_resizeFunction;



/**
 * @param {HTMLElement} viewport
 * @param {() => void} resizeFunction
 */
function lumora_viewport_set(viewport, resizeFunction) {
    lumora_viewport = viewport;
    lumora_viewport_resizeFunction = resizeFunction;
}

/**
 * @param {LumoraViewportMode|HTMLElement} arg
 */
function lumora_viewport_setMode(arg) {
    /** @type {LumoraViewportMode} */
    let mode;

    if (arg instanceof HTMLElement) {
        const element = arg;
        const aspectRatio = std_evaluate(element.dataset.aspectRatio);
        const name = element.dataset.name;

        mode = {
            name,
            aspectRatio
        }
    } else {
        mode = arg;
    }

    lumora_viewport_mode = mode;
    lumora_viewport_resize();
}

function lumora_viewport_onResize(callback) {
    lumora_viewport_listeners.push(callback);
}

function lumora_viewport_resize() {
    if (typeof lumora_viewport_resizeFunction === "function") {
        lumora_viewport_resizeFunction();
    }

    for (const viewportListener of lumora_viewport_listeners) {
        viewportListener(lumora_viewport_dimension);
    }
}
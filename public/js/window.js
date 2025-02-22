const EVENT_WINDOW_OPEN = 'window_open';



/** @type {HTMLElement} */
let windowOverlay;
/** @type {HTMLElement} */
let windowOverlayActive;
let isWindowModuleLoaded = false;
/** @type {{ fn: (HTMLElement) => void, arg: HTMLElement }[]} */
const queue = [];

window.addEventListener('load', () => {
    windowOverlay = jsml.div('window-overlay');
    windowOverlayActive = jsml.div('window-overlay-active');

    document.body.append(windowOverlay, windowOverlayActive);
    isWindowModuleLoaded = true;

    for (const backlog of queue) {
        backlog.fn(backlog.arg);
    }

    queue.length = 0;
});



/**
 * @param {HTMLElement} element
 */
function window_open(element) {
    if (!isWindowModuleLoaded) {
        queue.push({
            fn: window_open,
            arg: element
        });
        return;
    }

    element.classList.remove('hide');
    element.dispatchEvent(new CustomEvent(EVENT_WINDOW_OPEN));
    windowOverlayActive.appendChild(element);
}



/**
 * @param {HTMLElement} element
 */
function window_minimize(element) {
    if (!isWindowModuleLoaded) {
        queue.push({
            fn: window_minimize,
            arg: element
        });
        return;
    }

    console.warn('[TODO]: window_minimize');
}



/**
 * @param {HTMLElement} element
 */
function window_close(element) {
    if (!isWindowModuleLoaded) {
        queue.push({
            fn: window_close,
            arg: element
        });
        return;
    }

    element.classList.add('hide');
    windowOverlay.appendChild(element);
}



/**
 * @param {HTMLElement} element
 */
function window_init(element) {
    if (!isWindowModuleLoaded) {
        queue.push({
            fn: window_init,
            arg: element
        });
        return;
    }

    windowOverlay.appendChild(element);
    element.classList.add('hide');

    if (Boolean(element.dataset.windowDraggable)) {
        element.classList.add('draggable');
    }

    $('.close', element)?.addEventListener('click', () => {
        window_close(element);
    });


    $('.minimize', element)?.addEventListener('click', () => {
        window_minimize(element);
    });
}
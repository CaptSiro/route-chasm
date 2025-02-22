const EVENT_WINDOW_OPENED = 'windowOpened';
const EVENT_WINDOW_CLOSED = 'windowClosed';



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
    element.dispatchEvent(new CustomEvent(EVENT_WINDOW_OPENED));
    windowOverlayActive.appendChild(element);

    window.onbeforeunload = () => true;
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
    element.dispatchEvent(new CustomEvent(EVENT_WINDOW_CLOSED));

    window.onbeforeunload = null;
}

function window_requestAction(id, action) {
    const w = $("#" + id);
    if (w === null) {
        return;
    }

    switch (action) {
        case 'close': {
            window_close(w);
            break;
        }

        case 'open': {
            window_open(w);
            break;
        }
    }
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

    if (!element.parentElement.classList.contains("window-overlay-active")) {
        windowOverlay.appendChild(element);
        element.classList.add('hide');
    }

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



/**
 * @param {string} title
 * @param content
 * @param {boolean} isDraggable
 * @param {boolean} isMinimizable
 * @return {HTMLDivElement}
 */
function window_create(title, content, isDraggable = false, isMinimizable = false) {
    const controls = [
        jsml.button("close", Icon("nf-fa-close"))
    ];

    if (isMinimizable) {
        controls.unshift(
            jsml.button("minimize", Icon("nf-fa-window_minimize"))
        );
    }

    const w = jsml.div({
        class: "window hide",
        "x-init": "window_init"
    }, [
        jsml.div("head", [
            jsml.span(_, title),
            jsml.div("controls", controls)
        ]),
        jsml.div("content", content)
    ]);

    if (isDraggable) {
        w.dataset.windowDraggable = "true";
    }

    return w;
}



/**
 * @param {string} message
 * @return {Promise<void>}
 */
function window_alert(message) {
    return new Promise(resolve => {
        const w = window_create(
            "Alert",
            jsml.div("text-window", [
                jsml.h3(_, message),
                jsml.div("controls",
                    jsml.button({
                        onClick: () => window_close(w)
                    }, 'Ok')
                )
            ])
        );

        w.addEventListener(EVENT_WINDOW_CLOSED, () => resolve());
        window_open(w);
    });
}



/**
 * @param {string} message
 * @return {Promise<boolean>}
 */
async function window_confirm(message) {
    return new Promise(resolve => {
        let result = false;

        const w = window_create(
            "Confirm",
            jsml.div("text-window", [
                jsml.h3(_, message),
                jsml.div("controls", [
                    jsml.button({
                        onClick: () => {
                            result = true;
                            window_close(w);
                        }
                    }, 'Ok'),

                    jsml.button({
                        onClick: () => {
                            window_close(w);
                        }
                    }, 'Cancel'),
                ])
            ])
        );

        w.addEventListener(EVENT_WINDOW_CLOSED, () => resolve(result));
        window_open(w);
    });
}

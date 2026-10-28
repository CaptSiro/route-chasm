const EVENT_WINDOW_OPENED = 'windowOpened';
const EVENT_WINDOW_CLOSED = 'windowClosed';
const EVENT_WINDOW_ISSUE_CLOSE = 'windowIssueClose';
const EVENT_WINDOW_MINIMIZED = 'windowMinimized';
const EVENT_WINDOW_MAXIMIZED = 'windowMaximized';



/** @type {HTMLElement} */
let windowOverlay;
/** @type {HTMLElement} */
let windowOverlayActive;
let isWindowModuleLoaded = false;
/** @type {{ fn: (HTMLElement) => void, arg: HTMLElement }[]} */
const queue = [];

window.addEventListener('load', () => {
    const { div } = jsml;
    
    windowOverlay = div('window-overlay');
    windowOverlayActive = div('window-overlay-active');
    
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
    
    if (element.dataset.dialog === "true") {
        $(".root", document.body)?.toggleAttribute("inert", true);
        windowOverlayActive.classList.add("inert");
    }
    
    window_maximize(element);
    element.style.left = "50%";
    element.style.top = "50%";
    
    element.classList.remove('hide');
    element.dispatchEvent(new CustomEvent(EVENT_WINDOW_OPENED));
    windowOverlayActive?.appendChild(element);
    
    window_spotlight(element);
}

/**
 * @param {HTMLElement} element
 * @return {boolean}
 */
function window_isOpened(element) {
    return !element.classList.contains("hide");
}

/**
 * @param {HTMLElement} element
 * @param {number} x
 * @param {number} y
 * @private
 */
function window_move(element, x, y) {
    element.style.left = String(x / window.innerWidth * 100) + "%";
    element.style.top = String(y / window.innerHeight * 100) + "%";
}



/**
 * @param {HTMLElement} element
 * @param {Opt<HTMLElement>} maximize
 * @param {Opt<HTMLElement>} minimize
 */
function window_minimize(element, maximize = undefined, minimize = undefined) {
    if (!isWindowModuleLoaded) {
        queue.push({
            fn: window_minimize,
            arg: element
        });
        return;
    }
    
    const content = $(".content", element);
    if (!is(content)) {
        return;
    }
    
    const rect = element.getBoundingClientRect();
    content.classList.add('hide');
    element.style.height = "unset";
    const after = element.getBoundingClientRect();
    
    window_move(element, rect.x + after.width / 2, rect.y + after.height / 2);
    
    minimize ??= $('.minimize', element);
    maximize ??= $('.maximize', element);
    
    if (!is(minimize) || !is(maximize)) {
        return;
    }
    
    minimize.classList.add('hide');
    maximize.classList.remove('hide');
    element.dispatchEvent(new CustomEvent(EVENT_WINDOW_MINIMIZED));
}

/**
 * @param {HTMLElement} element
 * @param {Opt<HTMLElement>} maximize
 * @param {Opt<HTMLElement>} minimize
 */
function window_maximize(element, maximize = undefined, minimize = undefined) {
    if (!isWindowModuleLoaded) {
        queue.push({
            fn: window_minimize,
            arg: element
        });
        return;
    }
    
    const content = $(".content", element);
    if (!is(content)) {
        return;
    }
    
    const rect = element.getBoundingClientRect();
    content.classList.remove('hide');
    element.style.height = element.dataset.height ?? "unset";
    const after = element.getBoundingClientRect();
    
    window_move(element, rect.x + after.width / 2, rect.y + after.height / 2);
    
    minimize ??= $('.minimize', element);
    maximize ??= $('.maximize', element);
    
    if (!is(minimize) || !is(maximize)) {
        return;
    }
    
    maximize.classList.add('hide');
    minimize.classList.remove('hide');
    element.dispatchEvent(new CustomEvent(EVENT_WINDOW_MAXIMIZED));
}



/**
 * @param {HTMLElement} element
 * @param destroy
 */
function window_close(element, destroy = true) {
    if (!isWindowModuleLoaded) {
        queue.push({
            fn: window_close,
            arg: element
        });
        return;
    }
    
    if (element.dataset.dialog === "true") {
        $(".root", document.body)?.toggleAttribute("inert", false);
        windowOverlayActive.classList.remove("inert");
    }
    
    element.classList.add('hide');
    windowOverlay.appendChild(element);
    element.dispatchEvent(new CustomEvent(EVENT_WINDOW_CLOSED));
    
    window.onbeforeunload = null;
    
    if (destroy) {
        window_destroy(element);
    }
}

/**
 * @param {HTMLElement} child
 */
function window_issueClose(child) {
    child.dispatchEvent(new CustomEvent(EVENT_WINDOW_ISSUE_CLOSE));
}



/**
 * @param {HTMLElement} element
 */
function window_addDraggable(element) {
    element.classList.add('draggable');
    
    const head = $(".head", element);
    if (!is(head)) {
        return;
    }
    
    let isDragging = false;
    let start;
    let offsetX;
    let offsetY;
    
    head.addEventListener('pointerdown', event => {
        isDragging = true;
        
        start = element.getBoundingClientRect();
        offsetX = event.clientX - start.x;
        offsetY = event.clientY - start.y;
        
        head.setPointerCapture(event.pointerId);
    });
    
    head.addEventListener('pointerup', event => {
        isDragging = false;
        
        head.releasePointerCapture(event.pointerId);
    });
    
    head.addEventListener('pointermove', event => {
        if (!isDragging || !is(offsetX) || !is(offsetY) || !is(start)) {
            return;
        }
        
        const x = event.clientX - offsetX + start.width / 2;
        const y = event.clientY - offsetY + start.height / 2;
        
        window_move(element, x, y);
    });
    
    $(".controls", head)?.addEventListener('pointerdown', event => {
        event.stopImmediatePropagation();
    });
}

function window_spotlight(element) {
    if (!is(element.parentElement)) {
        return;
    }
    
    const windows = Array.from(element.parentElement.children);
    if (element.style.zIndex === String(windows.length + 1)) {
        return;
    }
    
    windows.sort((a, b) => {
        return Number(a.style.zIndex) - Number(b.style.zIndex);
    });
    
    for (let i = 0; i < windows.length; i++) {
        windows[i].style.zIndex = String(i + 1);
    }
    
    element.style.zIndex = String(windows.length + 1);
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
    
    if (element.id === '') {
        element.id = std_id_html(8);
    }
    
    if (!element.parentElement?.classList.contains("window-overlay-active")) {
        windowOverlay.appendChild(element);
        element.classList.add('hide');
    }
    
    if (Boolean(element.dataset.windowDraggable)) {
        window_addDraggable(element);
    }
    
    const destroyOnClose = Boolean(element.dataset.destroyOnClose ?? true);
    $('.close', element)?.addEventListener('click', () => {
        window_close(element, destroyOnClose);
    });
    
    element.addEventListener("pointerdown", () => window_spotlight(element));
    
    element.addEventListener(EVENT_WINDOW_ISSUE_CLOSE, event => {
        window_close(element);
        event.stopImmediatePropagation();
    });
    
    const minimize = $('.minimize', element);
    const maximize = $('.maximize', element);
    
    if (!is(minimize) || !is(maximize)) {
        return;
    }
    
    minimize.classList.remove('hide');
    maximize.classList.add('hide');
    
    minimize.addEventListener('click', () => {
        window_minimize(element, maximize, minimize);
    });
    
    maximize.addEventListener('click', () => {
        window_maximize(element, maximize, minimize);
    });
}

/**
 * @param {HTMLElement} child
 * @param {string} parentIdentifier
 */
function window_setParent(child, parentIdentifier) {
    const parent = $("#" + parentIdentifier);
    if (!is(parent)) {
        return;
    }
    
    parent.addEventListener(EVENT_WINDOW_CLOSED, () => window_close(child));
}



/**
 * @typedef {{
 isDialog?: boolean,
 isDraggable?: boolean,
 isMinimizable?: boolean,
 isResizable?: boolean,
 width?: string,
 height?: string,
 parent?: string,
 }} WindowSettings
 */

/** @type {WeakMap<HTMLElement, () => void>} disposes the effects of a window created by window_create */
const window_disposers = new WeakMap();

/**
 * The window is built inside its own root, disposed when the window is destroyed. Pass `content` as a builder
 * `() => content` for its effects to be owned by the window, content built before the call is not owned by it.
 *
 * @param {Content} title
 * @param {Content | (() => Content)} content
 * @param {WindowSettings} settings
 * @return {HTMLDivElement}
 */
function window_create(title, content, settings = {}) {
    // detached from the caller's owner, the window outlives e.g. the inspector it was opened from
    return createRoot(dispose => {
        const w = window_build(title, typeof content === "function" ? untrack(content) : content, settings);
        window_disposers.set(w, dispose);
        return w;
    });
}

/**
 * @param {Content} title
 * @param {Content} content
 * @param {WindowSettings} settings
 * @return {HTMLDivElement}
 */
function window_build(title, content, settings) {
    const width = settings.width ?? "300px";
    const height = settings.height ?? "unset";
    
    const w = html.element`
        <div
            class="window hide"
            data-width=${width}
            data-height=${height}
            style=${{ width, height }}
            data-window-draggable=${settings.isDraggable}
            data-dialog=${settings.isDialog}
        >
            <div class="head">
                <span>${title}</span>
                <div class="controls">
                    <${Show} when=${settings.isMinimizable}>
                        <button class="minimize">${nf("nf-fa-window_minimize", '_')}</button>
                        <button class="maximize">${nf("nf-fa-window_maximize", '█')}</button>
                    </>
                    
                    <button class="close">${nf("nf-fa-close", 'X')}</button>
                </div>
            </div>
            
            <div class="content">${content}</div>
        </div>
    `;
    
    if (is(settings.parent)) {
        window_setParent(w, settings.parent);
    }
    
    window_init(w);
    return w;
}

/**
 * @param {HTMLElement} element
 */
function window_destroy(element) {
    window_disposers.get(element)?.();
    window_disposers.delete(element);
    element.remove();
}



/**
 * @param {JsmlParentProps<{ title?: MaybeReactive<string> }>} props
 * @return {JsmlContent}
 */
function window_TextWindow(props) {
    return html`
        <div class="text-window">
            <${Show} when="${() => is(read(props.title))}">
                <h3>${props.title}</h3>
            </>
            
            ${props.children}
        </div>
    `;
}



/**
 * @param {string} message
 * @param {WindowSettings} settings
 * @return {Promise<void>}
 */
function window_alert(message, settings = {}) {
    return new Promise(resolve => {
        const w = window_create(
            "Alert",
            html`
                <${window_TextWindow} title="${message}">
                    <div class="controls">
                        <button onClick=${() => window_close(w)}>
                            Ok
                        </button>
                    </div>
                </>
            `,
            settings
        );
        
        w.addEventListener(EVENT_WINDOW_CLOSED, () => resolve());
        window_open(w);
    });
}



/**
 * @param {string} message
 * @param {WindowSettings} settings
 * @return {HTMLDivElement}
 */
function WindowNotice(message, settings = {}) {
    return window_create(
        "",
        window_TextWindow({ title: message }),
        settings
    );
}



/**
 * @param {string} message
 * @param {WindowSettings} settings
 * @return {Promise<string|undefined>}
 */
function window_prompt(message, settings = {}) {
    return new Promise(resolve => {
        const input = jsml.input({ type: 'text' });
        let result = undefined;
        
        const w = window_create(
            "Prompt",
            html`
                <${window_TextWindow}>
                    <${Label} label="${message}" target="${input}" />
                    ${input}
                    <div class="controls">
                        <button onClick="${() => {
                            result = input.value;
                            window_close(w);
                        }}">Ok</button>
                        
                        <button onClick="${() => window_close(w)}">
                            Cancel
                        </button>
                    </div>
                </>
            `,
            settings
        );
        
        w.addEventListener(EVENT_WINDOW_CLOSED, () => resolve(result));
        window_open(w);
    });
}



/**
 * @param {string} message
 * @param {WindowSettings} settings
 * @return {Promise<boolean>}
 */
async function window_confirm(message, settings = {}) {
    return new Promise(resolve => {
        let result = false;
        
        const w = window_create(
            "Confirm",
            html`
                <${window_TextWindow} title="${message}">
                    <div class="controls">
                        <button onClick="${() => {
                            result = true;
                            window_close(w);
                        }}">Ok</button>
                        
                        <button onClick="${() => window_close(w)}">Cancel</button>
                    </div>
                </>
            `,
            settings
        );
        
        w.addEventListener(EVENT_WINDOW_CLOSED, () => resolve(result));
        window_open(w);
    });
}



/**
 * @param {Impulse<number>} fileProgress
 * @param {WindowSettings} settings
 */
function window_fileUpload(fileProgress, settings = {}) {
    return window_create(
        'File upload',
        html`
            <${window_TextWindow} title="Uploading files...">
                <${Bar} percentage="${fileProgress}" />
            </>
        `,
        settings
    );
}



/**
 * @param {string} url
 * @param {?string} fileType
 * @return {Promise<string|null>}
 */
async function window_fileSelect(url, fileType = null) {
    return new Promise(resolve => {
        let result = null;
        
        const onClick = event => {
            const gridRow = event.target.closest(".grid-row");
            if (!is(gridRow)) {
                return;
            }
            
            const dataElement = $('.row', gridRow);
            if (!is(dataElement.dataset.fileHash)) {
                return;
            }
            
            if (gridRow.classList.contains("selected")) {
                result = null;
                gridRow.classList.remove("selected");
                return;
            }
            
            for (const row of $$('.grid-row', gridRow.parentElement)) {
                row.classList.remove("selected");
            }
            
            result = dataElement.dataset.fileHash;
            gridRow.classList.add("selected");
        };
        
        const onReload = event => {
            const breadCrumbs = Array.from($$('.nexus .bread-crumb', w));
            if (breadCrumbs.length <= 0) {
                return;
            }
            
            const last = breadCrumbs.at(-1);
            const link = $('[x-get]', last);
            if (!is(link)) {
                return;
            }
            
            event.stopImmediatePropagation();
            event.preventDefault();
            
            link.click();
        };
        
        const w = window_create(
            "File Select",
            html`
                <div class="content-window file-select-window">
                    <div x-get="${url}" x-event="jsmlLoad"></div>
                    
                    <div class="controls">
                        <button onClick="${() => window_close(w, true)}">Ok</button>
                        
                        <button onClick="${() => {
                            result = '';
                            window_close(w);
                        }}">Remove Selected</button>
                        
                        <button onClick="${() => {
                            result = null;
                            window_close(w);
                        }}">Cancel</button>
                    </div>
                </div>
            `,
            {
                isDialog: true,
                isResizable: true,
                isDraggable: true,
                isMinimizable: true,
                width: "500px",
            }
        );
        
        w.addEventListener('click', onClick);
        w.addEventListener(FS_RELOAD_CURRENT_DIRECTORY, onReload);
        w.addEventListener(EVENT_WINDOW_CLOSED, () => resolve(result));
        window_open(w);
    });
}

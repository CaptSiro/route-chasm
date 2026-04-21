/**
 * @template T
 * @param {Opt<T>} variable
 * @return {boolean}
 */
function is(variable) {
    return variable !== undefined && variable !== null;
}

/**
 * @param {string} selector
 * @param {Element | Document} element
 * @returns {HTMLAnchorElement | HTMLElement | HTMLAreaElement | HTMLAudioElement | HTMLBaseElement | HTMLQuoteElement | HTMLBodyElement | HTMLBRElement | HTMLButtonElement | HTMLCanvasElement | HTMLTableCaptionElement | HTMLTableColElement | HTMLDataElement | HTMLDataListElement | HTMLModElement | HTMLDetailsElement | HTMLDialogElement | HTMLDivElement | HTMLDListElement | HTMLEmbedElement | HTMLFieldSetElement | HTMLFormElement | HTMLHeadingElement | HTMLHeadElement | HTMLHRElement | HTMLHtmlElement | HTMLIFrameElement | HTMLImageElement | HTMLInputElement | HTMLLabelElement | HTMLLegendElement | HTMLLIElement | HTMLLinkElement | HTMLMapElement | HTMLMenuElement | HTMLMetaElement | HTMLMeterElement | HTMLObjectElement | HTMLOListElement | HTMLOptGroupElement | HTMLOptionElement | HTMLOutputElement | HTMLParagraphElement | HTMLPictureElement | HTMLPreElement | HTMLProgressElement | HTMLScriptElement | HTMLSelectElement | HTMLSlotElement | HTMLSourceElement | HTMLSpanElement | HTMLStyleElement | HTMLTableElement | HTMLTableSectionElement | HTMLTableCellElement | HTMLTemplateElement | HTMLTextAreaElement | HTMLTimeElement | HTMLTitleElement | HTMLTableRowElement | HTMLTrackElement | HTMLUListElement | HTMLVideoElement}
 */
function $(selector, element = document) {
    return element.querySelector(selector);
}

/**
 * @param {string} selector
 * @param {Element | Document} element
 * @returns {NodeListOf<HTMLElementTagNameMap[keyof HTMLElementTagNameMap]> |  NodeListOf<Element>}
 */
function $$(selector, element = document) {
    return element.querySelectorAll(selector);
}



class Todo extends Error {
}



function todo() {
    console.log(...arguments);
    throw new Todo();
}


/**
 * @param {number} x
 * @return {string}
 */
function std_percentage(x) {
    return (std_clamp(0, 1, x) * 100) + '%';
}

/**
 * @param {number} a1
 * @param {number} b1
 * @param {number} a2
 * @param {number} b2
 */
function std_rangeInRange(a1, b1, a2, b2) {
    if (b1 > a1) {
        const t = a1;
        a1 = b1;
        b1 = t;
    }

    if (b2 > a2) {
        const t = a2;
        a2 = b2;
        b2 = t;
    }

    return (a1 <= a2 && b2 <= b1)
        || (a2 <= a1 && b1 <= b2);
}

/**
 * Clamps number between given bounds
 * @param {Number} min
 * @param {Number} max
 * @param {Number} x
 * @returns {Number}
 */
function std_clamp(min, max, x) {
    if (x < min) {
        return min;
    }

    return x > max
        ? max
        : x;
}

/**
 * Evaluates a constant mathematical expression string and returns its numeric result.
 *
 * This function safely parses and computes expressions containing only
 * numeric literals and standard arithmetic operators (`+`, `-`, `*`, `/`, `%`, `()`, and `e` for scientific notation).
 *
 * Expressions containing variables, function calls, or invalid characters will throw an error.
 *
 * @example
 * std_evaluate("16/9");          // → 1.777...
 * std_evaluate("2 + 3 * 4");     // → 14
 * std_evaluate("(1 + 2) / 3");   // → 1
 * std_evaluate("5e2 + 100");     // → 600
 *
 * @throws {TypeError} If the input is not a string.
 * @throws {Error} If the expression contains invalid characters or cannot be evaluated as a number.
 *
 * @param {string} expression - The mathematical expression to evaluate.
 * @returns {number} The evaluated numeric result of the expression.
 */
function std_evaluate(expression) {
    if (typeof expression !== "string") {
        throw new TypeError("Expression must be a string");
    }

    if (!/^[\d+\-*/().\s%eE]+$/.test(expression)) {
        throw new Error("Invalid characters in expression");
    }

    const fn = Function(`"use strict"; return (${expression});`);
    const number = fn();
    if (typeof number !== "number") {
        throw new Error("Invalid numeric expression");
    }

    return number;
}

/**
 * Maps value `from` interval `to` interval
 * @param {Number} value
 * @param {Number} fromA
 * @param {Number} fromB
 * @param {Number} toA
 * @param {Number} toB
 * @return {Number}
 */
function std_map(value, fromA, fromB, toA, toB) {
    return ((value - fromA) / (fromB - fromA)) * (toB - toA) + toA;
}

/**
 * @param {number} a
 * @param {number} b
 * @param {number} x
 * @return {number}
 */
function std_linear(a, b, x) {
    return (b - x) / (b - a);
}

function std_lerp(a, b, t) {
    return a + (b - a) * t;
}

function std_vec2(x, y) {
    return new Vec2(x, y);
}

/**
 * @param {string} literal
 */
function std_range(literal) {
    const parts = literal
        .split(',')
        .map(x => x.trim())
        .filter(x => x.length !== 0);

    if (parts.length < 2) {
        return new Vec2(0, 0);
    }

    return new Vec2(Number(parts[0]), Number(parts[1]));
}

class Vec2 {
    x;
    y;

    constructor(x, y) {
        this.x = x;
        this.y = y;
    }

    dist2(to) {
        return Math.pow(to.x - this.x, 2) + Math.pow(to.y - this.y, 2);
    }

    clone() {
        return new Vec2(this.x, this.y);
    }

    copy(from) {
        this.x = from.x;
        this.y = from.y;
    }

    connect(to) {
        return new Vec2(to.x - this.x, to.y - this.y);
    }

    add(v) {
        this.x += v.x;
        this.y += v.y;
        return this;
    }

    sub(v) {
        this.x -= v.x;
        this.y -= v.y;
        return this;
    }

    clamp(n) {
        return std_clamp(Math.min(this.x, this.y), Math.max(this.x, this.y), n);
    }

    print(round = 100) {
        console.log(Math.round(this.x * round) / round, Math.round(this.y * round) / round);
    }
}



/**
 * Adds delay to running code synchronously
 * @param {Number} ms
 * @returns {Promise<void>}
 */
function std_wait(ms) {
    return new Promise(
        resolve => setTimeout(resolve, ms)
    );
}

/**
 * @param {number} a inclusive
 * @param {number} b exclusive
 * @returns {number}
 */
function std_random(a, b) {
    return std_lerp(a, b, Math.random());
}

/**
 * @param {number} a inclusive
 * @param {number} b exclusive
 * @returns {number}
 */
function std_randomInt(a, b) {
    return Math.floor(std_random(a, b));
}

/**
 * @template T
 * @param {T[] | string} array
 * @return {T|null}
 */
function std_randomItem(array) {
    if (array.length === 0) {
        return null;
    }

    return array[std_randomInt(0, array.length)];
}



const STD_CHAR_0 = '0'.charCodeAt(0);
const STD_CHAR_9 = '9'.charCodeAt(0);

function std_isWhitespace(char) {
    if (!is(char)) {
        return true;
    }

    switch (char) {
        case ' ':
        case '\n':
        case '\r':
        case '\f':
        case '\t':
            return true;
        default:
            return false;
    }
}

/**
 * @param {string} char
 */
function std_isDigit(char) {
    const code = char.charCodeAt(0);
    return STD_CHAR_0 <= code && code <= STD_CHAR_9;
}

function std_slug(text) {
    if (!text || typeof text !== "string") {
        return "";
    }

    // Normalize and remove diacritics
    let id = text
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");

    // Lowercase
    id = id.toLowerCase();

    // Replace non-alphanumeric characters with hyphen
    id = id.replace(/[^a-z0-9]+/g, "-");

    // Collapse multiple hyphens
    id = id.replace(/-+/g, "-");

    // Trim hyphens from start and end
    id = id.replace(/^-|-$/g, "");

    // IDs should not start with a digit
    if (/^[0-9]/.test(id)) {
        id = "id-" + id;
    }

    return id;
}



const STD_ID_CHARSET = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";
const STD_ID_CHARSET_SAFE = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
const STD_HTML_ID_CHARSET_FIRST = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
const STD_ID_POOL = new Set();

/**
 * Generates a unique Base64-safe identifier of the given length.
 * The generated ID is guaranteed to be unique until released.
 *
 * @param {number} length - Desired length of the identifier (recommended >= 6).
 * @param {string} charsetFirst
 * @param {string} charset
 * @param {Set<string>} pool
 * @returns {string} A unique Base64-safe identifier.
 */
function std_id(length, charsetFirst = STD_ID_CHARSET, charset = STD_ID_CHARSET, pool = STD_ID_POOL) {
    let id;

    do {
        id = std_randomItem(charsetFirst);

        for (let i = 1; i < length; i++) {
            id += std_randomItem(charset);
        }

    } while (pool.has(id));

    pool.add(id);
    return id;
}

function std_id_html(length, pool = STD_ID_POOL) {
    return std_id(length, STD_HTML_ID_CHARSET_FIRST, STD_ID_CHARSET, pool);
}

/**
 * Releases a previously generated identifier, allowing it to be reused.
 *
 * @param {string} id - The identifier to release.
 * @param {Set<string>} pool
 */
function std_id_free(id, pool = STD_ID_POOL) {
    if (!pool) {
        return;
    }

    pool.delete(id);
}



const std_relativeTimeFormat = new Intl.RelativeTimeFormat(undefined, { numeric: "auto" });

/**
 * @type {RelativeTimestamp[]}
 */
const STD_RELATIVE_TIMESTAMPS = [{
    amount: 60,
    name: "seconds"
}, {
    amount: 60,
    name: "minutes"
}, {
    amount: 24,
    name: "hours"
}, {
    amount: 7,
    name: "days"
}, {
    amount: 4.34524,
    name: "weeks"
}, {
    amount: 12,
    name: "months"
}, {
    amount: Number.POSITIVE_INFINITY,
    name: "years"
}];

/**
 * @param {Date} date
 * @returns {string}
 */
function std_dateRelative(date) {
    let duration = (date - new Date()) / 1000;

    for (let i = 0; i <= STD_RELATIVE_TIMESTAMPS.length; i++) {
        const relativeTimestamp = STD_RELATIVE_TIMESTAMPS[i];
        if (Math.abs(duration) < relativeTimestamp.amount) {
            return std_relativeTimeFormat.format(Math.round(duration), relativeTimestamp.name);
        }

        duration /= relativeTimestamp.amount;
    }

    return "Long time ago";
}



function std_fetch_window_settings() {
    const settings = { ...WINDOW_ALERT_SETTINGS };
    settings.width = '50vw';
    return settings;
}

/**
 * @param {RequestInit} options
 * @returns {RequestInit}
 */
function std_fetch_json(options = {}) {
    options.headers ??= {};
    options.headers['X-Response-Format'] = 'application/json';
    return options;
}

async function std_fetch_renderTextError(response) {
    await window_alert(await response.text(), std_fetch_window_settings());
}

async function std_fetch_renderHtmlError(response) {
    const domParser = new DOMParser();
    const dom = domParser.parseFromString(await response.text(), 'text/html');

    const content = jsml.div("pad-gap");
    content.innerHTML = dom.body.innerHTML;

    return new Promise(resolve => {
        const w = window_create(
            'Internal Server Error',
            content,
            std_fetch_window_settings()
        );

        w.addEventListener(EVENT_WINDOW_CLOSED, () => resolve());
        window_open(w);
    });
}

async function std_fetch_renderJsonError(response) {
    /** @type {InternalServerError} */
    const e = await response.json();
    const message = e.type === 'exception'
        ? e.exception + ': ' + e.message
        : 'Error: ' + e.message;
    const trace = e.type === 'exception'
        ? e.trace
        : [{ file: e.file, line: e.line }];

    const section = jsml.section("pad-gap", [
        jsml.h1({ style: { color: 'var(--error)' } }, message)
    ]);

    for (const t of trace) {
        section.append(jsml.div(_, `at ${t.file}: ${t.line}`));
    }

    return new Promise(resolve => {
        const w = window_create(
            'Internal Server Error',
            section,
            std_fetch_window_settings()
        );

        w.addEventListener(EVENT_WINDOW_CLOSED, () => resolve());
        window_open(w);
    });
}

/**
 * @param {Response} response
 * @return {Promise<boolean>}
 */
async function std_fetch_handleServerError(response) {
    if (response.status < 500) {
        return false;
    }

    const contentType = response.headers.get('Content-Type') ?? 'text/plain';
    if (contentType.startsWith('application/json') || contentType === 'json') {
        await std_fetch_renderJsonError(response);
    } else if (contentType.startsWith('text/html') || contentType === 'html') {
        await std_fetch_renderHtmlError(response);
    } else {
        await std_fetch_renderTextError(response);
    }

    return true;
}

/**
 * @param {Response} response
 */
function std_fetch_hasReload(response) {
    return response.headers.has('X-Reload');
}

function std_fetch_getNext(response) {
    return response.headers.get('X-Next');
}

function std_fetch_follow(response) {
    if (std_fetch_hasReload(response)) {
        location.reload();
        return;
    }

    const next = std_fetch_getNext(response);
    if (!is(next)) {
        return;
    }

    location.assign(next);
}



function std_dom_scrollToFragment() {
    const fragment = location.hash.startsWith('#')
        ? location.hash.substring(1)
        : location.hash;

    if (fragment.trim() === "") {
        return;
    }

    $('#' + fragment)?.scrollIntoView();
}

/**
 * @param {HTMLElement} element
 * @return {string}
 */
function std_dom_getWhitespaceTextContent(element) {
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

    for (let child of element.childNodes) {
        result += extractNode(child);
    }

    return result;
}

/**
 * @param {HTMLElement} child
 * @param {(element: HTMLElement) => boolean} validator
 * @param {boolean} includeChild
 * @returns {boolean}
 */
function std_dom_isDescendant(child, validator, includeChild = true) {
    if (includeChild && validator(child)) {
        return true;
    }

    let current = child.parentElement;
    while (true) {
        if (!is(current) || !(current instanceof HTMLElement)) {
            return false;
        }

        if (validator(current)) {
            return true;
        }

        current = current.parentElement;
    }
}

/**
 * @param {HTMLElement} child
 * @param {HTMLElement} parent
 */
function std_dom_scrollIntoView(child, parent) {
    const childRect = child.getBoundingClientRect();
    const parentRect = parent.getBoundingClientRect();

    const scroll = parent.scrollTop;

    const parentA = scroll;
    const parentB = parentA + parentRect.height;

    let current = child.parentElement;
    let parentTop = 0;

    while (true) {
        parentTop += current.offsetTop;

        if (!is(current) || current !== parent) {
            break;
        }

        current = current.parentElement;
    }

    const childA = child.offsetTop - parentTop;
    const childB = childA + childRect.height;

    if (std_rangeInRange(parentA, parentB, childA, childB)) {
        return;
    }

    let top = scroll;

    // childA        parentA  childB            parentB
    //    > ########### >       <                  <
    if (childA < parentA) {
        top -= Math.abs(parentA - childA);

        // parentA        childA  parentB            childB
        //    >             >       < ################ <
    } else if (parentB < childB) {
        top += Math.abs(childB - parentB);
    }

    parent.scrollTo({
        top,
        behavior: "smooth"
    });
}

/**
 * @param {HTMLElement} child
 * @param {(child: HTMLElement, parent: HTMLElement) => Opt<HTMLElement>} next
 * @param {SkipPredicate<HTMLElement>} skipPredicate
 * @returns {Opt<HTMLElement>}
 */
function std_dom_findChild(child, next, skipPredicate) {
    const parent = child.parentElement;
    const len = parent.children.length;
    if (len <= 0) {
        return null;
    }

    if (len <= 1) {
        return child;
    }

    let current = child;
    for (let i = 0; i < len; i++) {
        current = next(current, parent);
        if (!is(current)) {
            return null;
        }

        if (skipPredicate(current)) {
            continue;
        }

        return current;
    }

    return null;
}

/**
 * @param {HTMLElement} child
 * @param {HTMLElement} parent
 * @return {Opt<HTMLElement>}
 */
function std_dom_nextChild(child, parent) {
    return child.nextElementSibling ?? parent.children[0];
}

/**
 * @param {HTMLElement} child
 * @param {HTMLElement} parent
 * @return {Opt<HTMLElement>}
 */
function std_dom_previousChild(child, parent) {
    return child.previousElementSibling ?? parent.children[parent.children.length - 1];
}

/**
 * @param {string} selector
 * @return {Promise<HTMLElement>}
 */
function std_dom_onMount(selector) {
    return new Promise(resolve => {
        const element = $(selector);
        if (is(element)) {
            return resolve(element);
        }

        const observer = new MutationObserver(() => {
            const element = $(selector);
            if (is(element)) {
                resolve(element);
                observer.disconnect();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
}

/**
 * @param {HTMLElement} element
 * @param {string[]} classes
 * @returns {number} timeoutID
 */
function std_dom_pulseBackground(element, classes) {
    element.classList.add("transition-background", ...classes);

    return setTimeout(() => {
        element.classList.remove(...classes);

        setTimeout(() => {
            element.classList.remove("transition-background");
        }, 500);
    }, 1000);
}

/**
 * @param {HTMLElement} element
 * @param {boolean} dark
 * @returns {number} timeoutID
 */
function std_dom_validated(element, dark = false) {
    return std_dom_pulseBackground(element, ["validated", ...(dark ? ["darken"] : [])]);
}

function std_dom_rejected(element, dark = false) {
    return std_dom_pulseBackground(element, ["rejected", ...(dark ? ["darken"] : [])]);
}



/**
 * @param {() => void} listener
 * @param {boolean} once
 */
function std_onLoad(listener, once = true) {
    window.addEventListener("scriptLoad", listener, { once });
}

/**
 * @param {Record<string, any>} styles
 * @returns {string}
 */
function std_css(styles) {
    let buffer = "";

    for (const key in styles) {
        if (styles[key] === undefined) {
            continue;
        }

        buffer += `${std_camelToKebab(key)}: ${styles[key]};`;
    }

    return buffer;
}

/**
 * @param {string} string
 * @returns {string}
 */
function std_camelToKebab(string) {
    let buffer = "";

    for (let i = 0; i < string.length; i++) {
        if (uppercase.includesChar(string[i])) {
            buffer += "-" + string[i].toLowerCase();
            continue;
        }

        buffer += string[i];
    }

    return buffer;
}



/**
 * Calls function with provided element. This function expects `fn` to be one valid fully qualified function name
 *
 * @param {string | null | undefined} fn
 * @returns {Function}
 */
function std_getFunction(fn) {
    if (!is(fn)) {
        return undefined;
    }

    /** @type {any} */
    let context = window;

    for (const part of fn.split(".")) {
        context = context[part.trim()];

        if (context === undefined) {
            return undefined;
        }
    }

    return context;
}

/**
 * Parses function call and calls produced function on given element
 *
 * Example: `functionLiteral = 'console.log,custom'` will print `element` to console and call `custom(element)`
 *
 * @param {HTMLElement} element
 * @param {string} functionLiteral
 * @returns {string[]} Functions that could not be found
 */
function std_call(element, functionLiteral) {
    const notFound = [];

    for (const literal of functionLiteral.split(",")) {
        const fn = std_getFunction(literal.trim());
        if (!is(fn)) {
            notFound.push(literal.trim());
            continue;
        }

        fn(element, element.dataset);
    }

    return notFound;
}



/**
 * @template T, R
 * @param {T[]} array1
 * @param {R[]}  array2
 * @param {(a: T, b: R) => boolean} compareFunction
 * @returns {boolean}
 */
function std_arrayEquals(array1, array2, compareFunction = ((a, b) => a === b)) {
    if (array1.length !== array2.length) {
        return false;
    }

    for (let i = 0; i < array1.length; i++) {
        if (!compareFunction(array1[i], array2[i])) {
            return false;
        }
    }

    return true;
}



/**
 * @param {string|URL} url
 * @returns {URL}
 */
function std_jsonEndpoint(url) {
    if (typeof url === "string") {
        url = new URL(url);
    }

    url.searchParams.set('o', 'json');
    url.searchParams.set('i', 'json');
    return url;
}



/**
 * @return {BrowserType}
 */
function std_browser() {
    try {
        if (!!document.documentMode) {
            return !!window.StyleMedia
                ? "internet-explorer"
                : "edge";
        }

        if (typeof InstallTrigger !== "undefined") {
            return "firefox";
        }

        if ((!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(" OPR/") >= 0) {
            return "opera";
        }

        if (navigator.userAgent.indexOf("Edg") !== -1) {
            return "edge-chromium";
        }

        if (/constructor/i.test(window.HTMLElement)
            || (function (param) {
                return param.toString() === "[object SafariRemoteNotification]";
            })(!window["safari"] || (typeof safari !== "undefined" && window["safari"].pushNotification))) {

            return "safari";
        }
    } catch (ignored) {
    }

    return "chrome";
}
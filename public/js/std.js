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

class Todo extends Error {}

function todo() {
    console.log(...arguments);
    throw new Todo();
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
    if (typeof expression !== 'string') {
        throw new TypeError('Expression must be a string');
    }

    if (!/^[\d+\-*/().\s%eE]+$/.test(expression)) {
        throw new Error('Invalid characters in expression');
    }

    const fn = Function(`"use strict"; return (${expression});`);
    const number = fn();
    if (typeof number !== "number") {
        throw new Error('Invalid numeric expression');
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



const STD_ID_CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
const STD_ID_CHARSET_SAFE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
const STD_ID_POOL = new Set();

/**
 * Generates a unique Base64-safe identifier of the given length.
 * The generated ID is guaranteed to be unique until released.
 *
 * @param {number} length - Desired length of the identifier (recommended >= 6).
 * @param {string} charset
 * @param {Set<string>} pool
 * @returns {string} A unique Base64-safe identifier.
 */
function std_id(length, charset = STD_ID_CHARSET, pool = STD_ID_POOL) {
    let id;

    do {
        id = '';

        for (let i = 0; i < length; i++) {
            id += std_randomItem(charset);
        }

    } while (pool.has(id));

    pool.add(id);
    return id;
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



const std_relativeTimeFormat = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });

/**
 * @type {RelativeTimestamp[]}
 */
const STD_RELATIVE_TIMESTAMPS = [{
    amount: 60,
    name: 'seconds'
}, {
    amount: 60,
    name: 'minutes'
}, {
    amount: 24,
    name: 'hours'
}, {
    amount: 7,
    name: 'days'
}, {
    amount: 4.34524,
    name: 'weeks'
}, {
    amount: 12,
    name: 'months'
}, {
    amount: Number.POSITIVE_INFINITY,
    name: 'years'
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

    return 'Long time ago';
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

    for (const part of fn.split('.')) {
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
 */
function std_call(element, functionLiteral) {
    for (const literal of functionLiteral.split(',')) {
        const fn = std_getFunction(literal.trim());
        if (!is(fn)) {
            continue;
        }

        fn(element);
    }
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
 * @return {BrowserType}
 */
function std_browser() {
    try {
        if (!!document.documentMode) {
            return !!window.StyleMedia
                ? "internet-explorer"
                : "edge";
        }

        if (typeof InstallTrigger !== 'undefined') {
            return "firefox";
        }

        if ((!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0) {
            return "opera";
        }

        if (navigator.userAgent.indexOf("Edg") !== -1) {
            return "edge-chromium";
        }

        if (/constructor/i.test(window.HTMLElement)
            || (function (param) {
                return param.toString() === "[object SafariRemoteNotification]";
            })(!window['safari'] || (typeof safari !== 'undefined' && window['safari'].pushNotification))) {

            return "safari";
        }
    } catch (ignored) {}

    return "chrome";
}
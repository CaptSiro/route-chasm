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
 * @param {number} a
 * @param {number} b
 * @returns {number}
 */
function std_random(a, b) {
    return std_lerp(a, b, Math.random());
}

/**
 * @param {number} a
 * @param {number} b
 * @returns {number}
 */
function std_randomInt(a, b) {
    return Math.floor(std_random(a, b));
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
function std_scrollIntoView(child, parent) {
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

/**
 * @param {HTMLElement} element
 * @param {JsmlContentItem} item
 */
function jsml_addContentItem(element, item) {
    if (item === undefined) {
        return;
    }

    if (typeof item === "string") {
        element.append(document.createTextNode(item));
        return;
    }

    if (item instanceof Impulse) {
        if (item.value() instanceof Node) {
            let last = item.value();
            element.append(last);

            item.listen((n) => {
                element.replaceChild(n, last);
                last = n;
            });

            return;
        }

        const text = document.createTextNode(String(item.value()));
        element.append(text);

        item.listen(x => {
            text.textContent = String(x);
        });

        return;
    }

    if (item instanceof Node) {
        element.append(item);
        return;
    }

    element.append(document.createTextNode(String(item)));
}

/**
 * @param {HTMLElement} element
 * @param {JsmlContent} content
 */
function jsml_addContent(element, content) {
    if (!Array.isArray(content)) {
        // @ts-ignore Should be just singular object
        jsml_addContentItem(element, content);
        return;
    }

    for (const item of content) {
        jsml_addContentItem(element, item);
    }
}

/**
 * @param {string} key
 * @returns {string}
 */
function jsml_parse(key) {
    return key.substring(Number(key[0] === "\\"));
}

/**
 * @param {Element} element
 * @param {string} attribute
 * @param {any} value
 */
function jsml_setAttribute(element, attribute, value) {
    switch (typeof value) {
        case "undefined":
            break;
        case "boolean":
            element.toggleAttribute(attribute, value);
            break;
        case "string":
            element.setAttribute(attribute, value);
            break;
        default:
            element.setAttribute(attribute, String(value));
            break;
    }
}

/**
 * @param {Element} element
 * @param {JsmlProps | string} props
 */
function jsml_addProps(element, props) {
    if (props === undefined) {
        return;
    }

    if (typeof props === "string") {
        element.className = String(props);
        return;
    } else if ("class" in props) {
        element.className = String(props.class);
        delete props.class;
    }

    if (props.style !== undefined) {
        jsml_setAttribute(element, "style", std_css(props.style));
        delete props.style;
    }

    for (const key in props) {
        if (key[0] === "o" && key[1] === "n") {
            element.addEventListener(key.substring(2).toLowerCase(), props[key]);
            continue;
        }

        const k = std_camelToKebab(jsml_parse(key));
        if (typeof props[key] === "boolean") {
            jsml_setAttribute(element, k, props[key]);
            continue;
        }

        if (props[key] instanceof Impulse) {
            const v = props[key].value();
            if (v !== undefined) {
                jsml_setAttribute(element, k, v);
            }

            props[key].listen((x) => {
                jsml_setAttribute(element, k, x);
            });

            continue;
        }

        jsml_setAttribute(element, k, props[key]);
    }
}

/**
 * @returns {Jsml | {}}
 */
function jsml_init() {
    return new Proxy({}, {
        get(_, tag) {
            return (props, content) => {
                if (props instanceof HTMLElement) {
                    console.error(`Can not use HTMLElement as options. Caught at: ${String(tag)}`);
                    return document.createElement(String(tag));
                }

                const element = document.createElement(/** @type {keyof HTMLElementTagNameMap} */ tag);

                jsml_addProps(element, props);
                jsml_addContent(element, content);

                return element;
            }
        }
    });
}

const jsml = jsml_init();
const _ = undefined;
const JSML_EVENT_LOAD = 'jsmlLoad';
const JSML_EVENT_FETCHED = 'jsmlAdded';

/** @type {Map<string, Set<string>>} */
const sideloader_imported = new Map();
/** @type {Map<string, Set<HTMLElement>>} */
const sideloader_deferred = new Map();



window.addEventListener('load', () => {
    SideLoader.addImporter('js', (files, type) => {
        const script = jsml.script();
        script.src = SideLoader.createImportUrl(type, files);
        document.head.append(script);
    });

    SideLoader.addImporter('css', (files, type) => {
        const link = jsml.link();
        link.rel = "stylesheet";
        link.href = SideLoader.createImportUrl(type, files);
        document.head.append(link);
    });

    for (const importer of document.querySelectorAll("." + SideLoader.getImporterClass())) {
        const type = importer.dataset.type;
        if (!sideloader_imported.has(type)) {
            sideloader_imported.set(type, new Set());
        }

        const set = sideloader_imported.get(type);
        for (const file of String(importer.dataset.files).split(',')) {
            set.add(file);
        }
    }

    /**
     * @param {string} name
     * @returns {string}
     */
    function xAttr(name) {
        return 'x-' + name.toLowerCase();
    }
    
    const HTTP_METHODS = ["CONNECT", "DELETE", "GET", "HEAD", "OPTIONS", "PATCH", "POST", "PUT", "TRACE"]
    const HTTP_METHODS_ATTR = HTTP_METHODS.map(xAttr);
    const X_TARGET = 'x-target';
    const X_EVENT = 'x-event';
    const X_DATA = 'x-data';
    const X_SWAP = 'x-swap';
    const X_INIT = 'x-init';
    const X_PROCESSED = 'x-p';
    
    const attributes = [X_TARGET, X_EVENT, X_SWAP, X_INIT].concat(HTTP_METHODS_ATTR);
    const needProcessing = [X_INIT].concat(HTTP_METHODS_ATTR);

    /**
     * @typedef {{ httpMethod: string, url: string }} AjaxInfo
     */
    
    /**
     * @param {HTMLElement} element
     * @return {AjaxInfo | undefined}
     */
    function getAjaxInfo(element) {
        for (const httpMethod of HTTP_METHODS) {
            const attr = xAttr(httpMethod);
            
            if (element.hasAttribute(attr)) {
                return {
                    httpMethod,
                    url: element.getAttribute(attr),
                }
            }
        }
        
        return undefined;
    }

    const REQUIRE_HEADER_PARSE_REGEX = /([0-9A-Za-z_\-,]+)/g;

    /**
     * @param {string} content
     * @return {Map<string, string[]>}
     */
    function parseRequireHeader(content) {
        const groups = new Map();

        for (const type of content.split(';')) {
            const group = type.trim().match(REQUIRE_HEADER_PARSE_REGEX);
            if (!is(group) || typeof group[1] !== "string") {
                continue;
            }

            groups.set(group[0], group[1].split(','));
        }

        return groups;
    }

    /**
     * @param {string} header
     */
    function require(header) {
        const content = parseRequireHeader(header);

        content.forEach((files, type) => {
            const importer = SideLoader.getImporter(type);
            if (importer === undefined) {
                return;
            }

            if (!sideloader_imported.has(type)) {
                sideloader_imported.set(type, new Set());
            }

            const set = sideloader_imported.get(type);
            const unseen = [];

            for (const file of files) {
                if (set.has(file)) {
                    continue;
                }

                unseen.push(file);
                set.add(file);
            }
            
            if (unseen.length === 0) {
                return;
            }

            importer(unseen, type);
        });

        setTimeout(async () => {
            for (const delay of [200, 500, 1000, 2000, 4000]) {
                if (resolveDeferred()) {
                    return;
                }

                await std_wait(delay);
            }

            console.warn("Not resolved", Array.from(sideloader_deferred.keys()));
        }, 50);
    }

    function defer(element, functions) {
        for (const fn of functions) {
            const set = sideloader_deferred.get(fn);
            if (is(set)) {
                set.add(element);
                continue;
            }

            const s = new Set();
            s.add(element);
            sideloader_deferred.set(fn, s);
        }
    }

    function resolveDeferred() {
        if (sideloader_deferred.size === 0) {
            return true;
        }

        const resolved = [];
        sideloader_deferred.forEach((elements, fn) => {
            if (!is(std_getFunction(fn))) {
                return;
            }

            resolved.push(fn);
            for (const element of elements) {
                std_call(element, fn);
            }
        });

        for (const fn of resolved) {
            sideloader_deferred.delete(fn);
        }

        return sideloader_deferred.size === 0;
    }

    /**
     * @param {HTMLElement} element
     */
    function process(element) {
        if (!(element instanceof HTMLElement) || Boolean(element.getAttribute(X_PROCESSED))) {
            return;
        }

        element.setAttribute(X_PROCESSED, "1");

        const functionName = element.getAttribute(X_INIT);
        if (functionName !== null) {
            defer(element, std_call(element, functionName));
        }

        const ajaxInfo = getAjaxInfo(element);
        if (ajaxInfo === undefined) {
            return;
        }
        
        const event = element.hasAttribute(X_EVENT)
            ? element.getAttribute(X_EVENT)
            : 'click';
        
        element.addEventListener(event, async () => {
            const target = element.hasAttribute(X_TARGET)
                ? document.querySelector(element.getAttribute(X_TARGET))
                : element;

            const url = new URL(ajaxInfo.url, document.baseURI);
            url.searchParams.set('s', '');
            url.searchParams.set('jsml', '');
            url.searchParams.set('f', '');

            const body = element.hasAttribute(X_DATA)
                ? element.getAttribute(X_DATA)
                : undefined;

            const response = await fetch(url, {
                method: ajaxInfo.httpMethod,
                body
            });

            if (await std_fetch_handleServerError(response)) {
                return;
            }

            if (!response.ok) {
                console.warn(response.statusText);
                return;
            }

            const requireHeader = response.headers.get(SideLoader.getRequireHeader());
            if (requireHeader !== undefined && requireHeader !== null) {
                require(requireHeader);
            }
            
            const text = await response.text();
            if (target === null) {
                return;
            }

            const swap = element.hasAttribute(X_SWAP)
                ? element.getAttribute(X_SWAP)
                : 'inner';
            
            if (swap === "inner") {
                target.innerHTML = text;
                target.dispatchEvent(new CustomEvent(JSML_EVENT_FETCHED, {
                    bubbles: true,
                    detail: {
                        element: target.children[0]
                    }
                }));
                return;
            }

            target.outerHTML = text;
        });

        element.dispatchEvent(new CustomEvent(JSML_EVENT_LOAD, { bubbles: false }));
    }

    const selector = needProcessing
        .map(x => '[' + x + ']')
        .join(',');

    for (const element of document.querySelectorAll(selector)) {
        process(element);
    }

    new MutationObserver(mutations => {
        for (let i = 0; i < mutations.length; i++) {
            if (mutations[i].type === "attributes") {
                process(mutations[i].target);
                return;
            }

            if (mutations[i].type === "childList") {
                for (let j = 0; j < mutations[i].addedNodes.length; j++) {
                    const element = mutations[i].addedNodes[j];
                    if (!(element instanceof HTMLElement)) {
                        continue;
                    }

                    for (const x of element.querySelectorAll(selector)) {
                        process(x);
                    }
                    process(element);
                }
            }
        }
    }).observe(
        document.body,
        {
            subtree: true,
            childList: true,
            characterData: false,
            characterDataOldValue: false,
            attributeFilter: attributes
        }
    )
});
/**
 * SideLoader integration and `x-*` attributes (x-get, x-post, x-target, x-swap, x-init, …).
 * Moved verbatim from the original components/Jsml.js, loaded right after it (see Jsml.php).
 */
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
/**
 * @typedef {Impulse<any> | HTMLElement | Node | string | undefined} ContentItem
 *
 * @typedef {ContentItem | ArrayLike<ContentItem> | HTMLCollection} Content
 *
 * @typedef {{
 *     [key: string]: ((event: Event) => any) | Impulse<any> | any
 * } & {
 *     style?: Partial<CSSStyleDeclaration>
 * }} Attributes
 *
 * @typedef {Attributes | string | undefined} Props
 *
 * @typedef {{ [key in keyof HTMLElementTagNameMap]: (props?: Props | string, content?: Content) => HTMLElementTagNameMap[key] }} JSML
 */

/**
 * @returns {JSML | {}}
 */
function jsmlInit() {
    /**
     * @param {HTMLElement} element
     * @param {ContentItem} item
     */
    function addContentItem(element, item) {
        if (item === undefined) {
            return;
        }

        if (typeof item === "string") {
            element.textContent = item;
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
        }
    }

    /**
     * @param {HTMLElement} element
     * @param {Content} content
     */
    function addContent(element, content) {
        if (!Array.isArray(content)) {
            // @ts-ignore Should be just singular object
            addContentItem(element, content);
            return;
        }

        for (const item of content) {
            addContentItem(element, item);
        }
    }

    /**
     * @param {string} key
     * @returns {string}
     */
    function parse(key) {
        return key.substring(Number(key[0] === "\\"));
    }

    /**
     * @param {HTMLElement} element
     * @param {string} attribute
     * @param {any} value
     */
    function setAttribute(element, attribute, value) {
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
     * @param {Record<string, any>} styles
     * @returns {string}
     */
    function createCssString(styles) {
        let buffer = "";

        for (const key in styles) {
            if (styles[key] === undefined) {
                continue;
            }

            buffer += `${camelToKebab(key)}: ${styles[key]};`;
        }

        return buffer;
    }

    /**
     * @param {string} string
     * @returns {string}
     */
    function camelToKebab(string) {
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
     * @param {HTMLElement} element
     * @param {Props | string} props
     */
    function addProps(element, props) {
        if (props === undefined || typeof props === "string") {
            return;
        }

        if (props.style !== undefined) {
            setAttribute(element, "style", createCssString(props.style));
            delete props.style;
        }

        for (const key in props) {
            if (key[0] === "o" && key[1] === "n") {
                element.addEventListener(key.substring(2).toLowerCase(), props[key]);
                continue;
            }

            const k = camelToKebab(parse(key));
            if (typeof props[key] === "boolean") {
                setAttribute(element, k, props[key]);
                continue;
            }

            if (props[key] instanceof Impulse) {
                const v = props[key].value();
                if (v !== undefined) {
                    setAttribute(element, k, v);
                }

                props[key].listen((x) => {
                    setAttribute(element, k, x);
                });

                continue;
            }

            setAttribute(element, k, props[key]);
        }
    }

    return new Proxy({}, {
        get(_, tag) {
            return (props, content) => {
                if (props instanceof HTMLElement) {
                    console.error(`Can not use HTMLElement as options. Caught at: ${String(tag)}`);
                    return document.createElement(String(tag));
                }

                const element = document.createElement(/** @type {keyof HTMLElementTagNameMap} */ tag);

                if (typeof props === "string") {
                    element.className = String(props);
                } else if (props !== undefined && "class" in props) {
                    element.className = String(props.class);
                    delete props.class;
                }

                addProps(element, props);
                addContent(element, content);

                return element;
            }
        }
    });
}

const jsml = jsmlInit();



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

    /**
     * @param {string} name
     * @returns {string}
     */
    function xAttr(name) {
        return 'x-' + name.toLowerCase();
    }
    
    const HTTP_METHODS = ["CONNECT", "DELETE", "GET", "HEAD", "OPTIONS", "PATCH", "POST", "PUT", "TRACE"]
    const X_TARGET = 'x-target';
    const X_EVENT = 'x-event';
    const X_DATA = 'x-data';
    const X_SWAP = 'x-swap';
    
    const attributes = [X_TARGET, X_SWAP, X_EVENT].concat(HTTP_METHODS.map(xAttr));

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

    const REQUIRE_HEADER_PARSE_REGEX = /(\w+)\(([0-9a-f,]+)\)/g;

    /** @type {Map<string, Set<string>>} */
    const imported = new Map();

    /**
     * @param {string} content
     * @return {Map<string, string[]>}
     */
    function parseRequireHeader(content) {
        const groups = new Map();

        for (const group of content.matchAll(REQUIRE_HEADER_PARSE_REGEX)) {
            if (typeof group[2] !== "string") {
                continue;
            }

            groups.set(group[1], group[2].split(','));
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

            if (!imported.has(type)) {
                imported.set(type, new Set());
            }

            const set = imported.get(type);
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
    }

    /**
     * @param {HTMLElement} element
     */
    function process(element) {
        const ajaxInfo = getAjaxInfo(element);
        if (ajaxInfo === undefined) {
            return;
        }
        
        const target = element.hasAttribute(X_TARGET)
            ? document.querySelector(element.getAttribute(X_TARGET))
            : element;
        
        const event = element.hasAttribute(X_EVENT)
            ? element.getAttribute(X_EVENT)
            : 'click';

        const body = element.hasAttribute(X_TARGET)
            ? element.getAttribute(X_DATA)
            : undefined;
        
        const swap = element.hasAttribute(X_SWAP)
            ? element.getAttribute(X_SWAP)
            : 'inner';
        
        element.addEventListener(event, async () => {
            const url = new URL(ajaxInfo.url, document.baseURI);
            url.searchParams.set('s', '');

            const response = await fetch(url, {
                method: ajaxInfo.httpMethod,
                body
            });
            
            if (!response.ok) {
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
            
            if (swap === "inner") {
                target.innerHTML = text;
                return;
            }

            target.outerHTML = text;
        });
    }

    const selector = HTTP_METHODS
        .map(x => '[' + xAttr(x) + ']')
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
                    process(mutations[i].addedNodes[j]);
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
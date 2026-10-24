/**
 * @typedef {JsmlProps} Props
 * @typedef {JsmlContent} Content
 */

/**
 * @param {{ label: MaybeReactive<string>, isRemovable?: MaybeReactive<boolean>, onRemove?: (element: HTMLDivElement, event: Event) => boolean | void }} props
 *  Return false to cancel removing
 * @return {HTMLDivElement}
 */
function Tag({ label, isRemovable = true, onRemove = () => true }) {
    const remove = event => {
        if (onRemove(tag, event) === false) {
            return;
        }
        
        tag.remove();
    };
    
    const tag = html.element`
        <div class="tag">
            <span>${label}</span>
            <${Show} when=${isRemovable}>
                <button onClick=${remove}>
                    ${nf('nf-fa-close', '✕')}
                </button>
            </>
        </div>
    `;
    
    return tag;
}

/**
 * @param {string} nf
 * @param {string | undefined} fallback
 * @return {HTMLElement}
 */
function nf(nf, fallback = undefined) {
    return jsml.i('nf ' + nf, jsml.span(_, fallback));
}

/**
 * @param {{ nf: MaybeReactive<string>, fallback?: MaybeReactive<string> }} props
 */
function Icon({ nf, fallback}) {
    return html`
        <i class=${['nf', nf]}>
            <${Show} when=${() => is(read(fallback))}>
                <span>${fallback}</span>
            </>
        </i>
    `;
}

/**
 * @param {keyof HTMLElementTagNameMap} container
 * @param {string} url
 * @param {string} placeholder
 * @returns {HTMLElement}
 */
function Remote(container, url, placeholder) {
    return jsml[container]({
        'x-get': url,
        'x-event': JSML_EVENT_LOAD,
    }, placeholder);
}

/**
 * @param {{ percentage: MaybeReactive<number> }} props
 */
function Bar({ percentage }) {
    return html`
        <div class="progress-bar-container">
            <div
                class="progress-bar-fill"
                style=${() => `--fill: ${std_percentage(read(percentage))}`}
            ></div>
        </div>
    `;
}

/**
 * @param {JsmlTagProps<"input"> & { checked?: MaybeReactive<boolean>, label?: JsmlContent, id?: string }} props
 *  attributes of the <input> (checked, onChange, …) and the label
 * @returns {HTMLLabelElement}
 */
function CheckBox(props = {}) {
    const { label, ...p } = props;
    const id = props.id ?? std_id_html(8);
    
    return /** @type {HTMLLabelElement} */ (html.element`
        <label class="checkbox-container" for=${id}>
            <input type="checkbox" ...${p} id=${id} />
            <span>${label}</span>
        </label>
    `);
}

/**
 * @param {boolean} condition
 * @param {Content} content
 * @return {Content|undefined}
 */
function Optional(condition, content) {
    return condition
        ? content
        : undefined;
}

/**
 * @param {boolean} condition
 * @param {Content} content
 * @returns {Content[]}
 */
function Optionals(condition, content) {
    return (
        condition
            ? content
            : []
    );
}

/**
 * Asynchronously replace placeholder element with element(s) returned by `asyncFunction`
 * @param {()=>Promise<(Node | HTMLElement)[] | Node | HTMLElement | HTMLCollection>} asyncFunction
 * @param {HTMLElement} placeholder
 */
function Async(asyncFunction, placeholder = undefined) {
    const id = std_id_html(8);

    if (placeholder === undefined) {
        placeholder = jsml.div();
    }

    placeholder.classList.add(id);

    std_dom_onMount("." + id)
        .then(asyncFunction)
        .then(component => {
            if (component instanceof Node) {
                component = [component];
            }

            for (const c of component) {
                placeholder.parentElement.insertBefore(c, placeholder);
            }

            placeholder.remove();
            std_id_free(id);
        });

    return placeholder;
}

/**
 * @param {JsmlParentProps<{ href: MaybeReactive<string> }>} props
 * @returns {HTMLAnchorElement}
 */
function Link(props) {
    const { href, class: c, className, ...p } = props;
    
    return html.element`
        <a href="${href}" class=${[c ?? className, "link"]} ...${p}>
            ${props.children}
        </a>
    `;
}

const GLOBAL_VIEW_BOX = "0 0 500 500";

/**
 * @param {string} definitionID do not include '#'
 * @param {string} viewBox if undefined GLOBAL_VIEW_BOX is used
 * @param {Props} props
 * @see GLOBAL_VIEW_BOX
 * @return {SVGElement}
 */
function Svg(definitionID, viewBox = undefined, props = {}) {
    const svgElement = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svgElement.setAttribute("viewBox", viewBox ?? GLOBAL_VIEW_BOX);

    jsml_addProps(svgElement, props);

    const useElement = document.createElementNS("http://www.w3.org/2000/svg", "use");
    useElement.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", "#" + definitionID);

    svgElement.appendChild(useElement);
    return svgElement;
}

/**
 * @param {string} svgStringRepresentation
 * @param {string} id
 * @return {SVGGElement}
 */
function stringToSvgDef(svgStringRepresentation, id) {
    let svg = Html(svgStringRepresentation);

    do {
        if (svg.nodeName === "svg") break;
        svg = svg.nextSibling;
    } while (svg !== null && svg !== undefined);

    const g = document.createElementNS("http://www.w3.org/2000/svg", "g");
    g.id = id;

    if (svg?.innerHTML !== undefined) {
        g.innerHTML = svg.innerHTML;
    }

    return g;
}

/**
 * @param {JsmlProps<"input"> & { label: JsmlContent, className: string, checked?: MaybeReactive<boolean> }} props
 */
function Radio(props) {
    const { label, className, ...p } = props;
    let id = std_id_html(8);

    if ("id" in p) {
        id = p.id;
    } else {
        p.id = id;
    }
    
    return html.element`
        <label class=${['radio-container', className]} for=${id}>
            <input type="radio" ...${p}>
            <span>${label}</span>
        </label>
    `;
}

/**
 * @param {JsmlProps<"label"> & { label: MaybeReactive<string>, target: HTMLElement }} props
 */
function Label({ label, target, ...p }) {
    if (label === undefined) {
        return;
    }

    const id = std_id_html(8);
    
    target.id = id;
    return jsml.label({ for: id, ...p }, label);
}

/**
 * @param {JsmlParentProps<{ type: MaybeReactive<string> }>} props
 * @returns {HTMLElement}
 */
function Blockquote(props) {
    return html.element`
        <div class=${["blockquote", props.type]}>
            ${props.children}
        </div>
    `;
}



/**
 * @param {string} content
 * @param {boolean} isCollection
 * @returns {NodeListOf<ChildNode> | ChildNode}
 */
function Html(content, isCollection = false) {
    const template = document.createElement("template");
    template.innerHTML = content.trim();

    return isCollection
        ? template.content.childNodes
        : template.content.firstChild;
}
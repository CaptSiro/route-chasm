/**
 * @typedef {JsmlProps} Props
 * @typedef {JsmlContent} Content
 */

/**
 *
 * @param {string} label
 * @param {boolean} isRemovable
 * @param {(element: HTMLElementTagNameMap["div"], event: Event) => boolean} onRemove Return false to cancel removing
 * @return {HTMLDivElement}
 */
function Tag(label, isRemovable = true, onRemove = () => true) {
    const tag = jsml.div({ class: 'tag' }, jsml.span(_, label));
    if (!isRemovable) {
        return tag;
    }

    const onClick = event => {
        if (onRemove(tag, event) === false) {
            return;
        }

        tag.remove();
    }

    const button = jsml.button({ onClick }, '✕');
    tag.append(button);

    return tag;
}

/**
 * @param {string} nf
 * @param {string | undefined} fallback
 * @return {HTMLElement}
 */
function Icon(nf, fallback = undefined) {
    return jsml.i('nf ' + nf, jsml.span(_, fallback));
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
 * @param {Impulse} percentage
 */
function Bar(percentage) {
    const fill = jsml.div('progress-bar-fill');

    const updateFill = x => fill.style.setProperty('fill', std_percentage(x))

    updateFill(percentage.value());
    percentage.listen(updateFill);

    return jsml.div('progress-bar-container', fill);
}

/**
 * @param {boolean} checked
 * @param {string} label
 * @param {Props} props
 * @return {HTMLLabelElement}
 */
function CheckBox(checked, label, props = {}) {
    const id = std_id_html(8);

    return jsml.label({ class: "checkbox-container", for: id }, [
        jsml.input({ type: "checkbox", id, checked, ...props }),
        jsml.span(_, label)
    ]);
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
 * @param {string} href
 * @param {Content} content
 * @param {Props} props
 * @returns {HTMLElement}
 */
function Link(href, content, props = {}) {
    props.class = "link";
    props.href = href;
    return jsml.a(props, content);
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
 * @param {string} label
 * @param {string} value
 * @param {string} name
 * @param {string} className
 * @param {Props} inputProps
 */
function Radio(label, value, name, className = undefined, inputProps = {}) {
    let id = std_id_html(8);

    inputProps.type = "radio";
    inputProps.name = name;
    inputProps.value = value;

    if ("id" in inputProps) {
        id = inputProps.id;
    } else {
        inputProps.id = id;
    }

    return (
        jsml.label({
            class: "radio-container" +(className ? (" " + className) : ""),
            for: id,
        }, [
            jsml.input(inputProps),
            jsml.span(_, label)
        ])
    );
}

/**
 * @param {string | undefined} label
 * @param {HTMLElement} elementFor
 */
function LabelFactory(label, elementFor) {
    if (label === undefined) {
        return;
    }

    const id = std_id_html(8);

    elementFor.id = id;
    return jsml.label({ for: id }, label);
}

/**
 * @param {"error", "note"} type
 * @param {Content} content
 * @returns {HTMLElement}
 */
function Blockquote(type, content = undefined) {
    return (
        jsml.div("blockquote " + type, content)
    );
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
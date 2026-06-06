const MD_TAB_INDENT = " ".repeat(4);



class Markdown {
    /**
     * @param {MarkDownAst} ast
     */
    static *walkAst(ast) {
        for (const node of ast) {
            yield node;

            switch (node.type) {
                case "QUOTE":
                case "DECORATION":
                case "HEADING":
                case "PARAGRAPH": {
                    yield *Markdown.walkAst(node.ast);
                    break;
                }

                case "LINK": {
                    yield *Markdown.walkAst(node.label);
                    break;
                }

                case "LIST": {
                    for (const item of node.items) {
                        yield *Markdown.walkAst(item.ast);
                    }

                    break;
                }

                default: break;
            }
        }
    }

    /**
     * @param {MarkDownHeadingNode} node
     * @param {OnElementCreated} onElementCreated
     * @returns {HTMLHeadingElement}
     */
    static #createHtmlHeading(node, onElementCreated) {
        const tag = 'h' + std_clamp(1, 6, Math.round(node.level));
        const h = jsml[tag]('md-heading');
        Markdown.astToHtml(h, node.ast, onElementCreated);
        h.id = std_slug(h.textContent);
        return h;
    }

    /**
     * @param {MarkDownImageNode | *} node
     * @returns {HTMLImageElement}
     */
    static #createHtmlImage(node) {
        const { src, alt, title } = node;
        return jsml.img({
            class: 'md-image',
            src,
            alt,
            title
        });
    }

    /**
     * @param {HTMLElement} parent
     * @param {MarkDownAst} ast
     * @param {OnElementCreated} onElementCreated
     */
    static astToHtml(parent, ast, onElementCreated = () => {}) {
        const append = (element, node) => {
            onElementCreated(element, node);
            parent.append(element);
        }

        for (const node of ast) {
            switch (node.type) {
                case "PARAGRAPH": {
                    const p = jsml.p('md-paragraph');
                    Markdown.astToHtml(p, node.ast, onElementCreated);
                    append(p, node);
                    break;
                }

                case "TEXT": {
                    parent.append(document.createTextNode(node.text));
                    break;
                }

                case "HEADING": {
                    append(Markdown.#createHtmlHeading(node, onElementCreated), node);
                    break;
                }

                case "HORIZONTAL_LINE": {
                    append(jsml.hr('md-horizontal-line'), node);
                    break;
                }

                case "CODE": {
                    append(jsml.code('md-code', node.code), node);
                    break;
                }

                case "CODE_BLOCK": {
                    append(jsml.pre('md-code-block', node.code), node);
                    break;
                }

                case "HTML": {
                    const html = jsml.div('md-html');
                    html.innerHTML = node.html;
                    append(html, node);
                    break;
                }

                case "DECORATION": {
                    let decoration = jsml.span('md-decoration ' + node.style);
                    let container = decoration;

                    switch (node.style) {
                        case "BOLD": {
                            container = decoration = jsml.strong('md-bold');
                            break;
                        }

                        case "ITALIC": {
                            container = decoration = jsml.em('md-italic');
                            break;
                        }

                        case "ITALIC-BOLD": {
                            container = jsml.em('md-italic');
                            decoration = jsml.strong('md-bold', container);
                            break;
                        }

                        default: break;
                    }

                    Markdown.astToHtml(container, node.ast, onElementCreated);
                    append(decoration, node);
                    break;
                }

                case "QUOTE": {
                    const quote = jsml.blockquote({
                        class: 'md-quote',
                        dataIndent: node.indent
                    });

                    Markdown.astToHtml(quote, node.ast, onElementCreated);
                    append(quote, node);
                    break;
                }

                case "IMAGE": {
                    append(Markdown.#createHtmlImage(node), node);
                    break;
                }

                case "LINK": {
                    const { href, title } = node;
                    const a = jsml.a({
                        class: 'md-link',
                        href,
                        title
                    });

                    Markdown.astToHtml(a, node.label, onElementCreated);
                    append(a, node);
                    break;
                }

                case "LIST": {
                    const list = jsml.div('md-list');
                    let l;

                    for (const item of node.items) {
                        if (item.type === "ORDERED" && l?.tagName !== "OL") {
                            if (is(l)) {
                                list.append(l);
                            }

                            l = jsml.ol();
                        }

                        if (item.type === "UNORDERED" && l?.tagName !== "UL") {
                            if (is(l)) {
                                list.append(l);
                            }

                            l = jsml.ul();
                        }

                        const li = jsml.li({
                            class: 'md-list-item',
                            dataIndent: item.indent
                        });

                        Markdown.astToHtml(li, item.ast, onElementCreated);
                        l.append(li);
                        onElementCreated(li, item);
                    }

                    if (is(l)) {
                        list.append(l);
                    }

                    append(list, node);
                    break;
                }

                default: break;
            }
        }
    }

    /**
     * @param {HTMLElement} parent
     * @param {MarkDownAst} ast
     */
    static astToTableOfContents(parent, ast) {
        const list = jsml.ul('toc-list');
        const stack = [list];
        /** @type {MarkDownHeadingNode | undefined} */
        let last = undefined;

        for (const node of ast) {
            if (node.type !== "HEADING") {
                continue;
            }

            const h = Markdown.#createHtmlHeading(node, () => {});
            const item = jsml.li('toc-item',
                jsml.a({ href: "#" + h.id }, h.textContent)
            );

            if (!is(last)) {
                stack.at(-1).append(item);
                last = node;
                continue;
            }

            if (last.level < node.level) {
                const ul = jsml.ul('toc-list');
                stack.at(-1).append(ul);
                stack.push(ul);
            }

            if (last.level > node.level && stack.length >= 2) {
                stack.pop();
            }

            stack.at(-1).append(item);
            last = node;
        }

        parent.append(list);
    }

    /**
     * @param {HTMLElement} parent
     * @param {MarkDownAst} ast
     */
    static astToGallery(parent, ast) {
        for (const node of Markdown.walkAst(ast)) {
            if (node.type !== "IMAGE") {
                continue;
            }

            parent.append(Markdown.#createHtmlImage(node));
        }
    }



    /** @type {string} */
    #markdown;
    /** @type {MarkDownAst} */
    #ast;
    /** @type {HTMLElement} */
    #html;
    /** @type {HTMLElement} */
    #tableOfContents;
    /** @type {HTMLElement} */
    #gallery;



    constructor(markdown) {
        this.#markdown = markdown;
    }

    #parse() {
        if (is(this.#ast)) {
            return this.#ast;
        }

        const tokenizer = new MarkDownTokenizer();
        const parser = new MarkDownAstParser();

        return this.#ast = parser.createAst(
            tokenizer.tokenize(this.#markdown)
        );
    }

    /**
     * @param {OnElementCreated} onElementCreated
     * @return {HTMLElement}
     */
    getHtml(onElementCreated = () => {}) {
        if (is(this.#html)) {
            return this.#html;
        }

        this.#html = jsml.div('md');
        Markdown.astToHtml(this.#html, this.#parse(), onElementCreated);

        return this.#html;
    }

    getTableOfContents(onElementCreated) {
        if (is(this.#tableOfContents)) {
            return this.#tableOfContents;
        }

        this.#tableOfContents = jsml.div('toc');
        Markdown.astToTableOfContents(this.#tableOfContents, this.#parse());

        return this.#tableOfContents;
    }

    getGallery() {
        if (is(this.#gallery)) {
            return this.#gallery;
        }

        this.#gallery = jsml.div('gallery');
        Markdown.astToGallery(this.#gallery, this.#parse());

        return this.#gallery;
    }
}
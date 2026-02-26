const MD_TAB_INDENT = " ".repeat(4);



class MarkDown {
    /**
     * @param {HTMLElement} parent
     * @param {MarkDownAstNode[]} ast
     */
    static astToHtml(parent, ast) {
        for (const node of ast) {
            switch (node.type) {
                case "PARAGRAPH": {
                    const p = jsml.p('md-paragraph');
                    MarkDown.astToHtml(p, node.ast);
                    parent.append(p);
                    break;
                }

                case "TEXT": {
                    parent.append(document.createTextNode(node.text));
                    break;
                }

                case "HEADING": {
                    const tag = 'h' + std_clamp(1, 6, Math.round(node.level));
                    const h = jsml[tag]('md-heading');
                    MarkDown.astToHtml(h, node.ast);
                    parent.append(h);
                    break;
                }

                case "HORIZONTAL_LINE": {
                    parent.append(
                        jsml.hr('md-horizontal-line')
                    );
                    break;
                }

                case "CODE": {
                    parent.append(
                        jsml.code('md-code', node.code)
                    );
                    break;
                }

                case "CODE_BLOCK": {
                    parent.append(
                        jsml.pre('md-code-block', node.code)
                    );
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

                    parent.append(decoration);
                    MarkDown.astToHtml(container, node.ast);
                    break;
                }

                case "QUOTE": {
                    const quote = jsml.blockquote({
                        class: 'md-quote',
                        dataIndent: node.indent
                    });

                    parent.append(quote);
                    MarkDown.astToHtml(quote, node.ast);
                    break;
                }

                case "IMAGE": {
                    const { src, alt, title } = node;
                    const image = jsml.img({
                        class: 'md-image',
                        src,
                        alt,
                        title
                    });

                    parent.append(image);
                    break;
                }

                case "LINK": {
                    const { href, title } = node;
                    const a = jsml.a({
                        class: 'md-link',
                        href,
                        title
                    });

                    parent.append(a);
                    MarkDown.astToHtml(a, node.label);
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

                        MarkDown.astToHtml(li, item.ast);
                        l.append(li);
                    }

                    if (is(l)) {
                        list.append(l);
                    }

                    parent.append(l);
                    break;
                }

                default: break;
            }
        }
    }

    parse(markDown) {
        console.time("MarkDown");
        const tokenizer = new MarkDownTokenizer();
        const parser = new MarkDownAstParser();
        parser.debug = true;
        const ast = parser.createAst(
            tokenizer.tokenize(markDown)
        );

        const md = jsml.div('md');
        MarkDown.astToHtml(md, ast);

        console.timeEnd("MarkDown");
        return md;
    }
}



/**
 * @param {HTMLElement} element
 */
function md_markDownDisplay(element) {
    const code = $(".mark-down-code", element);
    const parse = $("button", element);
    const display = $(".mark-down-display", element);

    parse?.addEventListener("click", () => {
        if (!is(display) || !is(code)) {
            return;
        }

        display.textContent = "";
        display.append(new MarkDown().parse(std_dom_contentEditableText(code)));
    });

    setTimeout(() => parse?.click(), 50);
}
class WTextEditor extends Widget {
// use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef {((string | any[] | LinkJSON | WidgetJSON)[] | string)[]} TextEditorJSONContent
     * lines []
     *
     *    -> line contents []
     *
     *    -> line contents []
     *
     *    -> line contents []
     */

    /**
     * @typedef TextEditorJSONType
     * @property {TextEditorJSONContent} content
     * @property {boolean=} forceSingleLine force whole content into single line: for titles, links, and such
     * @property {undefined | "simple" | "fancy"} mode
     * @property {string=} hint
     * @property {boolean=} disableLinks
     * @property {boolean=} doRequestNewLine
     *
     * @typedef {TextEditorJSONType & WidgetJSON} TextEditorJSON
     */

    /**
     * @param {string} content
     * @param {boolean} empty
     * @returns {HTMLDivElement}
     */
    static #newLine(content = "", empty = true) {
        const div = document.createElement("div");
        div.addEventListener("keydown", evt => {
            if (evt.key === "Backspace") {
                evt.preventDefault();
            }
        });

        if (content === "" && !empty) {
            div.innerHTML = "<br>";
        } else {
            div.textContent = content;
        }

        return div;
    }

    /**
     * @param {TextEditorJSONContent} contentArray
     * @param {boolean} forceSingleLine
     * @param {boolean} editable
     * @returns {HTMLElement[]}
     */
    #parseContent(contentArray, forceSingleLine = false, editable = false) {
        if (!contentArray || contentArray.length === 0) {
            return [WTextEditor.#newLine("", false)];
        }

        const lines = [];

        for (const linesContents of contentArray) {
            if (linesContents.length === 0) {
                lines.push(WTextEditor.#newLine("", false));
                continue;
            }

            if (typeof linesContents === "string") {
                lines.push(WTextEditor.#newLine(linesContents, false));
                continue;
            }

            let line = WTextEditor.#newLine();

            for (const element of linesContents) {
                if (typeof element === "string") {
                    line.appendChild(document.createTextNode(element));
                    continue;
                }

                if (element instanceof Array) {
                    line.appendChild(WTextDecoration.unpack(element, this).rootElement);
                    continue;
                }

                if (element.type !== undefined) {
                    if (!widgets.exists(element.type)) {
                        // const request = widgets.request(element.type);
                        //
                        // const replacement = jsml.span("widget-loading", `[Loading ${element.type}...]`);
                        // line.appendChild(replacement);
                        //
                        // request.then(() => {
                        //     line.insertBefore(widgets.get(element.type).build(element, this, editable).rootElement, replacement);
                        //     replacement.remove();
                        // });
                        //
                        // if (element === linesContents[linesContents.length - 1]) {
                        //     line.appendChild(jsml.br());
                        // }
                        continue;
                    }

                    line.appendChild(widgets.get(element.type).build(element, this, editable).rootElement);
                    if (element === linesContents[linesContents.length - 1]) {
                        line.appendChild(jsml.br());
                    }
                }
            }

            lines.push(line);
        }

        return lines;
    }

    /**
     * @param {Node} parent
     * @returns {Text | HTMLBRElement | Node | undefined}
     */
    static getLastTextNode(parent) {
        if (parent.childNodes.length === 0) return;

        const reversed = Array.from(parent.childNodes);
        for (let i = reversed.length - 1; i >= 0; i--) {
            if (reversed[i].nodeType === Node.TEXT_NODE || reversed[i].nodeName === "BR") {
                return reversed[i];
            }

            if (reversed[i].nodeType === Node.ELEMENT_NODE) {
                return this.getLastTextNode(reversed[i]);
            }
        }
    }



    /**
     * @type {HTMLElement}
     */
    #article;
    /**
     * @type {TextEditorJSON}
     */
    #json;

    #editable;

    static #class = "w-text-editor";

    /**
     * @param {TextEditorJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(jsml.div(WTextEditor.#class), parent, editable);

        this.rootElement.setAttribute("hint", json.hint ?? "Lorem ipsum...");

        this.#json = json;
        this.#editable = editable;

        this.removeMargin();
        this.removeMargin();

        this.childSupport = "none";

        this.#article = (
            jsml.article(_, this.#parseContent(json.content, json.forceSingleLine, editable))
        );
        this.rootElement.appendChild(this.#article);

        if (editable !== true) {
            return;
        }

        this.#article.setAttribute("contenteditable", "true");
        this.#article.setAttribute("spellcheck", "false");
        this.#article.classList.add("edit");

        // append fancy text gui
        if (json.mode === "fancy") {
            this.rootElement.classList.add("fancy");
            this.appendFancyGUI();
        }

        if (this.#article.textContent === "" || this.#article.textContent === "​") {
            this.rootElement.classList.add("show-hint");
        }

        this.#article.addEventListener("keydown", this.forceSingleLineHandler());
        this.#article.addEventListener("keydown", evt => {
            this.actionHandler(evt);

            if (this.#json.doRequestNewLine && evt.key === "Enter") {
                if (evt.shiftKey) {
                    return;
                }

                this.dispatch("next-default");
                // this.parentWidget.nextDefault?.call(this.parentWidget, this);
                evt.preventDefault();
            }
        });

        this.#article.addEventListener("keyup", (evt) => {
            if (this.rootElement.classList.contains("show-hint") && evt.key === "Backspace" || evt.key === "Delete") {
                this.dispatch("remove");
            }

            if (this.#article.textContent !== "") {
                this.rootElement.classList.remove("show-hint");
                return;
            }

            this.rootElement.classList.add("show-hint");
        });

        this.#article.addEventListener("paste", evt => {
            if (this.#article !== document.activeElement && !this.#article.contains(document.activeElement)) return;

            evt.preventDefault();
            this.actionHandler(evt);

            const pasteString = (evt.clipboardData || window.clipboardData).getData("text");
            const selection = window.getSelection();

            if (!selection.rangeCount) {
                return;
            }

            selection.deleteFromDocument();

            if (json.disableLinks !== true && WLink.isValidLink(pasteString)) {
                const linkWidget = WLink.build({
                    text: pasteString,
                    url: pasteString
                }, this, editable);

                selection.getRangeAt(0).insertNode(linkWidget.rootElement);
            } else {
                selection.getRangeAt(0).insertNode(document.createTextNode(pasteString));
            }

            selection.collapseToEnd();
        });
    }

    /**
     * @callback TextEditorListener
     * @param {WTextEditor} context
     */
    /**
     * @type {Object.<string, TextEditorListener[]>}
     */
    #listeners = {};

    /**
     * @param {string} event
     * @param {TextEditorListener} listener
     */
    addListener(event, listener) {
        if (this.#listeners[event] === undefined) {
            this.#listeners[event] = [listener];
            return;
        }

        this.#listeners[event].push(listener);
    }

    /**
     * @param {string} event
     */
    dispatch(event) {
        if (this.#listeners[event] === undefined) return;
        for (const listener of this.#listeners[event]) {
            listener(this);
        }
    }


    /**
     * @param {undefined | "simple" | "fancy"} mode
     */
    setMode(mode) {
        this.#json.mode = mode;
        if (mode === "fancy") {
            this.rootElement.classList.add("fancy");
            this.appendFancyGUI();
            return;
        }

        this.rootElement.classList.remove("fancy");
        this.removeFancyGUI();
    }

    /**
     * @param {boolean} forceSingleLine
     */
    setForceSingleLine(forceSingleLine) {
        this.#json.forceSingleLine = forceSingleLine;

        if (this.#editable === true && forceSingleLine === true) {
            this.#article.addEventListener("keydown", this.forceSingleLineHandler());
        } else {
            this.#article.removeEventListener("keydown", this.forceSingleLineHandler());
        }
    }

    resetContent() {
        this.#article.textContent = "";
        this.#article.append(
            ...this.#parseContent(this.#json.content, this.#json.forceSingleLine)
        );

        if (this.#article.textContent === "" || this.#article.textContent === "​") {
            this.rootElement.classList.add("show-hint");
        }
    }

    forceSingleLineHandler() {
        return (event => {
            if (event.key !== "Enter") {
                return;
            }

            if (this.#json.forceSingleLine) {
                event.preventDefault();
            }
        }).bind(this);
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WTextEditor}
     */
    static default(parent, editable) {
        return WTextEditor.build({
            content: []
        }, parent, editable);
    }

    /**
     * @override
     * @param {TextEditorJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WTextEditor}
     */
    static build(json, parent, editable = false) {
        return new WTextEditor(json, parent, editable);
    }

    /**
     * @override
     * @returns {Content}
     */
    get inspectorHTML() {
        return (
            NotInspectorAble()
        );
    }

    /**
     * @override
     * @returns {TextEditorJSON}
     */
    save() {
        return {
            type: "WTextEditor",
            content: this.parseLines(),
            forceSingleLine: this.#json.forceSingleLine,
            mode: this.#json.mode
        };
    }

    isSelectAble() {
        return false;
    }



    /**
     * @override
     */
    focus() {
        const range = document.createRange();
        const selection = window.getSelection();

        let lastTextNode = WTextEditor.getLastTextNode(this.#article);
        if (lastTextNode.nodeName === "BR") {
            const textNode = document.createTextNode("​");
            lastTextNode.parentElement.insertBefore(textNode, lastTextNode);
            lastTextNode = textNode;
        }

        range.setStart(lastTextNode, lastTextNode.textContent.length);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);
    }

    appendFancyGUI() {
        this.rootElement.style.position = "relative";
        this.rootElement.appendChild(
            jsml.div("gui fancy-controls", [
                jsml.button({ onClick: this.handleDecoration(WTextDecoration.types.BOLD) }, [
                    jsml.strong(_, "B")
                ]),

                jsml.button({ onClick: this.handleDecoration(WTextDecoration.types.ITALIC) }, [
                    jsml.i(_, "I")
                ]),

                jsml.button({ onClick: this.handleDecoration(WTextDecoration.types.UNDERLINE) }, [
                    jsml.u(_, "U")
                ]),

                jsml.button({
                    onClick: this.handleDecoration(WTextDecoration.types.LINE_THROUGH),
                    style: "text-decoration: line-through;"
                }, "S"),

                jsml.button({ onClick: this.handleDecoration(WTextDecoration.types.CODE) }, [
                    jsml.code(_, "<>")
                ])
            ])
        );
    }

    removeFancyGUI() {
        const fancy = this.rootElement.querySelector(".gui.fancy-controls");
        if (fancy === null) {
            return;
        }

        fancy.remove();
    }

    normalize() {
        if (this.#article.childNodes.length === 0) {
            this.#article.appendChild(WTextEditor.#newLine());
            return;
        }

        let buffer = jsml.div();

        for (const node of [...this.#article.childNodes]) {
            if (node.nodeName === "BR") {
                node.remove();
                continue;
            }

            if (node.nodeType === Node.TEXT_NODE || node.nodeName === "SPAN" || node.nodeName === "A") {
                buffer.append(node);
                continue;
            }

            if (buffer.childNodes.length !== 0) {
                this.#article.insertBefore(buffer, node);
                buffer = jsml.div();
            }
        }

        if (buffer.childNodes.length !== 0) {
            this.#article.append(buffer);
        }
    }

    static splitInMiddle(string, startIndex, endIndex) {
        const children = [];

        const start = string.substring(0, startIndex);
        if (start !== "") {
            children.push(WTextDecoration.build({
                text: start
            }, this).rootElement);
        }

        const decorated = WTextDecoration.build({
            text: string.substring(startIndex, endIndex)
        }, this);
        children.push(decorated.rootElement);

        const end = string.substring(endIndex);
        if (end !== "") {
            children.push(WTextDecoration.build({
                text: end
            }, this).rootElement);
        }

        return {
            children,
            decorated
        };
    }

    getTrace(child) {
        const trace = [child];

        while (this.#article !== child || (child !== null && child !== undefined)) {
            child = child.parentElement;

            if (child === this.#article) {
                break;
            }

            trace.unshift(child);
        }

        return trace;
    }

    static orderNodes(order, indexes, node, n, nodes, start = 0) {
        for (let i = start; i < nodes.length; i++) {
            if (node !== nodes[i]) continue;

            order.push(node);
            indexes.push(n);

            nodes.splice(i, 1);
            WTextEditor.orderNodes(order, indexes, node, n, nodes, i);
            return;
        }
    }

    /**
     * @param {string} type
     * @param {HTMLElement} container
     * @param {...HTMLElement} nodes
     */
    static getPosition(type, container, ...nodes) {
        const order = [];
        const indexes = [];

        let n = 0;
        for (const node of container.childNodes) {
            this.orderNodes(order, indexes, node, n, nodes);
            n++;
        }

        return {
            order, indexes
        };
    }

    /**
     * @param {number} start
     * @param {number} end
     * @param {(child: Node, lineStart: boolean)=>number | void} callback
     */
    forEachLineChild(start, end, callback) {
        let lineStart = true;
        if (start === end) {
            for (const child of [...this.#article.children[start].childNodes]) {
                const doBreak = callback(child, lineStart);
                lineStart = false;

                if (doBreak === 0) {
                    return;
                }

                if (doBreak === 1) {
                    return;
                }
            }
            return;
        }

        lines : for (let i = start; i <= end; i++) {
            for (const child of [...this.#article.children[i].childNodes]) {
                const doBreak = callback(child, lineStart);
                lineStart = false;

                if (doBreak === 0) {
                    break;
                }

                if (doBreak === 1) {
                    break lines;
                }
            }
            lineStart = true;
        }
    }

    handleDecoration(type) {
        return () => {
            const selection = window.getSelection();

            this.normalize();

            if (selection.anchorNode === selection.focusNode) {
                const string = selection.focusNode.textContent;
                let parent = selection.focusNode.parentElement;

                if (parent === this.#article) {
                    const span = WTextDecoration.build({
                        text: "",
                        class: [type]
                    }, this);

                    if (selection.anchorNode.childNodes.length >= 1) {
                        selection.anchorNode.insertBefore(span.rootElement, selection.anchorNode.childNodes[0]);
                    } else {
                        selection.anchorNode.appendChild(span.rootElement);
                    }

                    span.toggleDecoration(type, true);
                    span.focus();
                    return;
                }

                const startIndex = Math.min(selection.focusOffset, selection.anchorOffset);
                const endIndex = Math.max(selection.focusOffset, selection.anchorOffset);


                if (parent.widget === undefined) {
                    const split = WTextEditor.splitInMiddle(
                        string,
                        startIndex,
                        endIndex
                    );

                    parent.textContent = "";
                    parent.append(...split.children);
                    split.decorated.toggleDecoration(type);
                    return;
                }


                if (endIndex - startIndex === string.length) {
                    parent.widget?.toggleDecoration(type);
                    return;
                }


                if (parent.widget instanceof WTextDecoration) {
                    const decorations = Object.values(WTextDecoration.types);
                    const parentsClasses = Array.from(parent.classList.values())
                        .filter(clazz => decorations.indexOf(clazz) !== -1);
                    const spanSplit = WTextEditor.splitInMiddle(string, startIndex, endIndex, type);

                    for (const child of spanSplit.children) {
                        child.classList.add(...parentsClasses);
                        parent.parentElement.insertBefore(child, parent);
                    }

                    parent.remove();
                    spanSplit.decorated.toggleDecoration(type);
                    return;
                }
            }

            const anchorTrace = this.getTrace(selection.anchorNode);
            const focusTrace = this.getTrace(selection.focusNode);

            const position = WTextEditor.getPosition(type, this.#article, anchorTrace[0], focusTrace[0]);
            let isAnchorFirst = position.order[0] === anchorTrace[0];
            if (anchorTrace[0] === focusTrace[0]) {
                const rowPosition = WTextEditor.getPosition(type, focusTrace[0], anchorTrace[1], focusTrace[1]);
                isAnchorFirst = rowPosition.order[0] === anchorTrace[1];
            }

            let elementCount = 0;
            let classCount = 0;
            let doCount = false;
            this.forEachLineChild(position.indexes[0], position.indexes[1], child => {
                if (child.nodeName === "BR") return;

                if (child === (isAnchorFirst ? anchorTrace : focusTrace)[1]) doCount = true;

                if (doCount) {
                    elementCount++;
                    if (child.nodeType === Node.ELEMENT_NODE && child?.classList?.contains(type)) classCount++;

                    if (child === (!isAnchorFirst ? anchorTrace : focusTrace)[1]) {
                        return 1;
                    }
                }
            });

            const anchorOffset = selection.anchorOffset;
            const focusOffset = selection.focusOffset;
            const typeSetState = elementCount !== classCount;
            let doModification = false;
            const selectionNodes = {
                start: undefined, end: undefined
            };


            this.forEachLineChild(position.indexes[0], position.indexes[1], child => {
                let isHead = child === (isAnchorFirst ? anchorTrace : focusTrace)[1];
                let isTail = child === (!isAnchorFirst ? anchorTrace : focusTrace)[1];
                const isEdge = (isHead || isTail);

                if (isHead) {
                    doModification = true;
                }

                if (!doModification) return;

                if (child.nodeType === Node.TEXT_NODE) {
                    const split = WTextEditor.splitInMiddle(
                        child.textContent,
                        isAnchorFirst
                            ? child === anchorTrace[1]
                                ? anchorOffset
                                : 0
                            : child === focusTrace[1]
                                ? focusOffset
                                : 0,
                        isAnchorFirst
                            ? child === focusTrace[1]
                                ? focusOffset
                                : child.textContent.length
                            : child === anchorTrace[1]
                                ? anchorOffset
                                : child.textContent.length
                    );

                    const parent = child.parentElement;
                    parent.textContent = "";
                    parent.append(...split.children);
                    split.decorated.toggleDecoration(type, typeSetState);

                    if (isEdge) {
                        selectionNodes[isHead ? "start" : "end"] = split.decorated.rootElement;
                    }

                    return 0;
                }

                if (child.widget !== undefined && child.widget.split) {
                    const result = child.widget.split(
                        isAnchorFirst
                            ? child === anchorTrace[1]
                            : child === focusTrace[1],
                        !isAnchorFirst
                            ? child === anchorTrace[1]
                            : child === focusTrace[1],
                        isAnchorFirst
                            ? anchorOffset
                            : focusOffset,
                        !isAnchorFirst
                            ? anchorOffset
                            : focusOffset
                    );

                    if (result.elements.length === 1) {
                        result.elements[0].toggleDecoration(type, typeSetState);
                    } else {
                        for (const element of result.elements) {
                            child.parentElement.insertBefore(element.rootElement, child);
                        }

                        child.remove();
                        result.elements[result.index].toggleDecoration(type, typeSetState);
                    }

                    if (isEdge) {
                        selectionNodes[isHead ? "start" : "end"] = result.elements[result.index].rootElement;
                    }
                }

                if (isTail) {
                    return 1;
                }
            });
            for (let i = position.indexes[0]; i <= position.indexes[1]; i++) {
                if (
                    this.#article.children[i].childNodes.length === 0
                    || this.#article.children[i].childNodes[0]?.nodeName === "BR"
                ) continue;

                let child = this.#article.children[i].childNodes[0];
                while (child !== null) {
                    const sibling = child.nextSibling;

                    if (sibling !== null && sibling.widget !== undefined && child.widget !== undefined) {
                        let childClasses, siblingClasses;
                        if (child.widget.getFilteredClasses) {
                            childClasses = child.widget.getFilteredClasses();
                        }

                        if (sibling.widget.getFilteredClasses) {
                            siblingClasses = sibling.widget.getFilteredClasses();
                        }

                        if (std_arrayEquals(childClasses, siblingClasses)) {
                            child.append(...sibling.childNodes);
                            sibling.remove();
                            continue;
                        }
                    }

                    if (child.childNodes.length === 0) child.remove();

                    child = sibling;
                }
            }

            selection.removeAllRanges();
        };
    }

    parseLines() {
        this.normalize();
        const lines = [];

        for (const line of this.#article.children) {
            let lineContents = [];

            const childrenOfLine = Array.from(line.childNodes);
            for (const child of childrenOfLine) {
                if (child.nodeType === Node.TEXT_NODE) {
                    if (child.textContent === "") continue;

                    lineContents.push(child.textContent);
                    continue;
                }

                if (child.nodeName === "BR" && child !== childrenOfLine[childrenOfLine.length - 1]) {
                    lines.push(lineContents);
                    lineContents = [];
                    continue;
                }

                if (child.widget instanceof WTextDecoration) {
                    lineContents.push(...child.widget.saveCompact());
                    continue;
                }

                if (child.widget) {
                    lineContents.push(child.widget.save());
                }
            }

            lines.push(lineContents);
        }

        return lines;
    }
}



widgets.define("WTextEditor", WTextEditor);
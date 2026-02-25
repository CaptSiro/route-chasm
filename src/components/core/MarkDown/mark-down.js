const MD_TAB_INDENT = " ".repeat(4);



/**
 * @param {string} chars
 * @param {string} line
 * @param {number} position
 */
function md_readRepetition(chars, line, position) {
    let str = '';
    for (let i = position; i < line.length; i++) {
        if (!chars.includes(line[i])) {
            return str;
        }

        str += line[i];
    }

    return str;
}



/**
 * @param {MarkDownToken[]} tokens
 * @returns {MarkDownToken[][]}
 */
function md_tokenizeLines(tokens) {
    let line = [];
    const lines = [];

    for (const token of tokens) {
        if (token.type === "NEW_LINE") {
            lines.push(line);
            line = [];
            continue;
        }

        line.push(token);
    }

    return lines;
}



class MarkDownTokenizer {
    /** @type {MarkDownToken[]} */
    #tokens;
    /** @type {number} */
    #position;
    /** @type {string} */
    #text;



    #appendText(string) {
        this.#text += string;
        this.#position += string.length;
    }

    #addTextToken() {
        if (this.#text.length === 0) {
            return;
        }

        for (const char of this.#text) {
            if (!std_isWhitespace(char)) {
                this.#tokens.push({ type: "TEXT", literal: this.#text });
                this.#text = "";
                return;
            }
        }

        this.#tokens.push({ type: "WHITESPACE", literal: this.#text });
        this.#text = "";
    }

    /**
     * @param {MarkDownToken} token
     * @param {boolean} updatePosition
     */
    #pushToken(token, updatePosition = true) {
        this.#addTextToken();
        this.#tokens.push(token);

        if (!updatePosition) {
            return;
        }

        this.#position += token.literal.length;
    }

    /**
     * @param {string} char
     * @param {string} line
     * @param {MarkDownTokenType} tokenType
     * @param {boolean | null} followedByWhitespace
     */
    #readRepetition(char, line, tokenType, followedByWhitespace = true) {
        const literal = md_readRepetition(char, line, this.#position);

        if (is(followedByWhitespace)) {
            if (std_isWhitespace(line[this.#position + literal.length]) !== followedByWhitespace) {
                this.#appendText(literal);
                return;
            }

            this.#position++;
        }

        this.#pushToken({ type: tokenType, literal });
    }

    tokenize(markDown) {
        this.#tokens = [];

        for (const line of markDown.split("\n")) {
            if (line.trim() === "") {
                this.#tokens.push({ type: "PARAGRAPH", literal: "" });
                this.#tokens.push({ type: "NEW_LINE", literal: "\n" });
                continue;
            }

            this.#position = 0;
            this.#text = "";

            for (; this.#position < line.length;) {
                const char = line[this.#position];

                switch (char) {
                    case "#": {
                        this.#readRepetition(char, line, "HEADING");
                        break;
                    }
                    case ">": {
                        this.#readRepetition(char, line, "QUOTE_BLOCK", null);
                        break;
                    }

                    case "*": {
                        const literal = md_readRepetition('*', line, this.#position);
                        const before = std_isWhitespace(line[this.#position - 1]);
                        const after = std_isWhitespace(line[this.#position + literal.length]);

                        if (before && after) {
                            // _**_
                            if (literal.length !== 1) {
                                this.#appendText(literal);
                                break;
                            }

                            // _*_
                            this.#pushToken({ type: "LIST_ITEM", literal: '*' });
                            this.#position++;
                            break;
                        }

                        if (!before) {
                            if (!after) {
                                // x*x
                                this.#pushToken({ type: "DECORATION", literal });
                                break;
                            }

                            // x*_
                            this.#pushToken({ type: "DECORATION_END", literal });
                            break;
                        }

                        // _*x
                        this.#pushToken({ type: "DECORATION_START", literal });
                        break;
                    }
                    case "_": {
                        const literal = md_readRepetition('_', line, this.#position);
                        const before = std_isWhitespace(line[this.#position - 1]);
                        const after = std_isWhitespace(line[this.#position + literal.length]);

                        if (!before) {
                            if (!after) {
                                // x*x
                                this.#pushToken({ type: "DECORATION", literal });
                                break;
                            }

                            // x*_
                            this.#pushToken({ type: "DECORATION_END", literal });
                            break;
                        }

                        if (!after) {
                            // _*x
                            this.#pushToken({ type: "DECORATION_START", literal });
                            break;
                        }

                        this.#appendText(literal);
                        break;
                    }
                    case "`": {
                        const literal = md_readRepetition(char, line, this.#position);
                        if (literal.length <= 2) {
                            this.#pushToken({ type: "CODE", literal });
                            break;
                        }

                        if (literal.length === 3) {
                            this.#pushToken({ type: "CODE_BLOCK", literal });
                            break;
                        }

                        this.#appendText(literal);
                        break;
                    }

                    case "!": {
                        this.#pushToken({ type: "EXCLAMATION", literal: char });
                        break;
                    }
                    case "[": {
                        this.#pushToken({ type: "BRACKET_START", literal: char });
                        break;
                    }
                    case "]": {
                        this.#pushToken({ type: "BRACKET_END", literal: char });
                        break;
                    }
                    case "(": {
                        this.#pushToken({ type: "PARENTHESIS_START", literal: char });
                        break;
                    }
                    case ")": {
                        this.#pushToken({ type: "PARENTHESIS_END", literal: char });
                        break;
                    }
                    case "\"": {
                        this.#pushToken({ type: "QUOTE", literal: char });
                        break;
                    }

                    default: {
                        if (std_isDigit(char)) {
                            let digits = char;
                            for (let i = this.#position + 1; i < line.length; i++) {
                                if (!std_isDigit(line[i])) {
                                    break;
                                }

                                digits += line[i];
                            }

                            if (line[this.#position + digits.length] === "." && std_isWhitespace(line[this.#position + digits.length + 1])) {
                                this.#pushToken({ type: "LIST_ITEM", literal: digits + "." });
                                break;
                            }

                            this.#appendText(digits);
                            break;
                        }

                        this.#appendText(char);
                    }
                }
            }

            this.#pushToken({ type: "NEW_LINE", literal: "\n" });
        }

        return this.#tokens;
    }
}



class MarkDownAstParser {
    /** @type {MarkDownToken[]} */
    #tokens;
    /** @type {number} */
    #tokenCursor;
    /** @type {boolean} */
    #allowBlockParsing;

    /**
     * @param {boolean} allowBlockParsing
     */
    constructor(allowBlockParsing = true) {
        this.#allowBlockParsing = allowBlockParsing;
        this.debug = false;
    }



    #advance(jump = 1) {
        this.#tokenCursor += jump;
        // console.trace('advance', jump, this.#tokenCursor);
    }

    /**
     * @param {MarkDownTokenType} type
     */
    #isBlock(type) {
        switch (type) {
            case "PARAGRAPH":
            case "HEADING":
            case "LIST_ITEM":
            case "CODE_BLOCK":
            case "QUOTE_BLOCK":
                return true;
            default:
                return false;
        }
    }

    /**
     * @return {MarkDownToken | undefined}
     */
    #nextBlockToken(position = this.#tokenCursor) {
        if (this.#current("WHITESPACE") || this.#current("NEW_LINE")) {
            position += 1;
        }

        return this.#tokens[position];
    }

    /**
     * @param {number} start
     * @param {boolean} advanceCursor
     * @return {MarkDownToken[]}
     */
    #readLine(start = this.#tokenCursor, advanceCursor = true) {
        let i = start;
        let newLineEncountered = false;

        for (; i < this.#tokens.length; i++) {
            if (this.#tokens[i].type === "NEW_LINE") {
                newLineEncountered = true;
            }

            const block = this.#nextBlockToken(i);
            if (newLineEncountered && this.#isBlock(block?.type)) {
                break;
            }
        }

        const tokens = this.#tokens.slice(start, i);

        if (advanceCursor) {
            this.#advance(tokens.length + 1);
        }

        return tokens;
    }

    /**
     * @param {MarkDownTokenType} tokenType
     * @param {number} start
     * @param {boolean} advanceCursor
     * @return {MarkDownToken[]}
     */
    #readUntil(tokenType, start = this.#tokenCursor, advanceCursor = true) {
        let i = start;
        let newLineEncountered = false;

        for (; i < this.#tokens.length; i++) {
            if (this.#tokens[i].type === "NEW_LINE") {
                newLineEncountered = true;
            }

            const token = this.#tokens[i];
            if (token.type === tokenType) {
                break;
            }

            const block = this.#nextBlockToken();
            if (newLineEncountered && this.#isBlock(block?.type)) {
                break;
            }
        }

        const tokens = this.#tokens.slice(start, i);

        if (advanceCursor) {
            this.#advance(tokens.length + 1);
        }

        return tokens;
    }

    /**
     * @param {MarkDownToken[]} tokens
     * @param {number} start
     * @param {number} end
     * @returns {string}
     */
    #text(tokens, start = 0, end = Number.POSITIVE_INFINITY) {
        let text = '';

        for (let i = start; i < tokens.length && i < end; i++) {
            text += tokens[i].literal;
        }

        return text;
    }



    /**
     * @param {MarkDownTokenType} type
     * @param {boolean} outOfBounds
     */
    #current(type, outOfBounds = false) {
        const i = this.#tokenCursor;
        if (!(0 <= i && i < this.#tokens.length)) {
            return outOfBounds;
        }

        return is(this.#tokens[i])
            && this.#tokens[i].type === type;
    }

    /**
     * @param {MarkDownTokenType} type
     * @param {number} jump
     * @param {boolean} outOfBounds
     */
    #next(type, jump = 1, outOfBounds = false) {
        const i = this.#tokenCursor + jump;
        if (i >= this.#tokens.length) {
            return outOfBounds;
        }

        return is(this.#tokens[i])
            && this.#tokens[i].type === type;
    };

    /**
     * @param {MarkDownTokenType} type
     * @param {number} jump
     * @param {boolean} outOfBounds
     */
    #previous(type, jump = 1, outOfBounds = false) {
        const i = this.#tokenCursor - jump;
        if (i < 0) {
            return outOfBounds;
        }

        return is(this.#tokens[i])
            && this.#tokens[i].type === type;
    };

    /**
     * @param {MarkDownTokenType} type
     * @returns {boolean}
     */
    #isBlockNext(type) {
        const isNewLineBefore = this.#previous("NEW_LINE", 1, true);
        const isBlockNext = this.#current(type)
            || (this.#current('WHITESPACE') && this.#next(type))

        return isNewLineBefore && isBlockNext;
    }

    #blockPosition() {
        return this.#tokenCursor + Number(this.#current("WHITESPACE"));
    }

    /**
     * @return {MarkDownParagraphNode | null}
     */
    #parseParagraph() {
        this.#advance();
        const parser = new MarkDownAstParser(false);
        const ast = parser.createAst(this.#readLine());

        if (ast[0]?.type === "NEW_LINE") {
            ast.shift();
        }

        if (ast.at(-1)?.type === "NEW_LINE") {
            ast.pop();
        }

        if (ast.length === 0) {
            return null;
        }

        return {
            type: "PARAGRAPH",
            ast
        };
    }

    /**
     * @return {MarkDownHeadingNode}
     */
    #parseHeading() {
        const position = this.#blockPosition();
        const level = this.#tokens[position].literal.length;

        const parser = new MarkDownAstParser(false);
        const ast = parser.createAst(this.#readLine(position + 1));

        return {
            type: "HEADING",
            level,
            ast
        };
    }

    /**
     * @param {string} whitespace
     */
    #indent(whitespace) {
        let spaces = 0;

        for (const char of whitespace.replaceAll("\t", MD_TAB_INDENT)) {
            if (char === " ") {
                spaces++;
            }
        }

        return Math.floor(spaces / MD_TAB_INDENT);
    }

    /**
     * @return {MarkDownListItemNode}
     */
    #parseListItem() {
        let indent = 0;
        if (this.#current("WHITESPACE")) {
            indent = this.#indent(this.#tokens[this.#tokenCursor].literal);
        }

        const position = this.#blockPosition();
        const type = this.#tokens[position].literal === '*'
            ? "UNORDERED"
            : "ORDERED";

        const parser = new MarkDownAstParser(false);
        console.log();
        const ast = parser.createAst(this.#readLine(position + 1));

        return {
            type,
            indent,
            ast
        };
    }

    /**
     * @return {MarkDownListNode}
     */
    #parseList() {
        /** @type {MarkDownListItemNode[]} */
        const items = [];

        if (this.#current("WHITESPACE")) {
            this.#advance();
        }

        while (true) {
            items.push(this.#parseListItem());

            if (!this.#isBlockNext("LIST_ITEM")) {
                break;
            }
        }

        return {
            type: "LIST",
            items
        };
    }

    /**
     * @returns {MarkDownCodeBlockNode}
     */
    #parseCodeBlock() {
        const position = this.#blockPosition();

        let code = '';
        for (const token of this.#readUntil("CODE_BLOCK")) {
            code += token.literal;
        }

        return {
            type: "CODE_BLOCK",
            code
        };
    }

    /**
     * @return {MarkDownQuoteNode}
     */
    #parseQuote() {
        const position = this.#blockPosition();
        const indent = this.#tokens[position].literal.length;

        const parser = new MarkDownAstParser(false);
        const ast = parser.createAst(this.#readLine(position + 1));

        return {
            type: "QUOTE",
            indent,
            ast
        };
    }

    /**
     * @returns {MarkDownLinkNode | MarkDownTextNode}
     */
    #parseLink() {
        let offset = 1;
        const parsingFailed = {
            type: "TEXT",
            text: '['
        };

        const labelTokens = this.#readUntil(
            "BRACKET_END",
            this.#tokenCursor + offset,
            false
        );
        offset += labelTokens.length;

        const isHrefNext = this.#next("BRACKET_END", offset)
            && this.#next("PARENTHESIS_START", offset + 1);
        if (!isHrefNext) {
            this.#advance();
            return parsingFailed;
        }

        offset += 2;
        const hrefTokens = this.#readUntil(
            "PARENTHESIS_END",
            this.#tokenCursor + offset,
            false
        );
        offset += labelTokens.length;

        const isHrefClosed = this.#next("PARENTHESIS_END", offset);
        if (!isHrefClosed) {
            this.#advance();
            return parsingFailed;
        }

        this.#advance(offset + 1);

        const parser = new MarkDownAstParser(false);
        const label = parser.createAst(labelTokens);
        const labelText = this.#text(labelTokens);

        let href = '';

        let quotePosition = -1;
        for (let i = 0; i < hrefTokens.length; i++) {
            const token = hrefTokens[i];

            if (href.type === "QUOTE") {
                quotePosition = i;
                break;
            }

            href += token.literal;
        }

        let title = undefined;
        if (quotePosition >= 0) {
            for (let i = 0; i < hrefTokens.length; i++) {
                if (hrefTokens[i].type === "QUOTE") {
                    break;
                }

                title += hrefTokens[i].literal;
            }
        }

        return {
            type: "LINK",
            href,
            label,
            labelText,
            title,
        };
    }

    /**
     * @returns {MarkDownImageNode | MarkDownTextNode}
     */
    #parseImage() {
        this.#advance();
        const link = this.#parseLink();
        if (link.type === "TEXT") {
            return {
                type: "TEXT",
                text: '!' + link.text
            };
        }

        return {
            type: "IMAGE",
            src: link.href,
            alt: link.labelText,
            title: link.title,
        }
    }

    /**
     * @return {MarkDownCodeNode | MarkDownTextNode}
     */
    #parseCode() {
        this.#advance();
        const codeTokens = this.#readUntil("CODE", this.#tokenCursor, false);

        if (!this.#next("CODE", codeTokens.length)) {
            return {
                type: "TEXT",
                text: '`'
            };
        }

        this.#advance(codeTokens.length + 1);
        const code = this.#text(codeTokens);

        return {
            type: "CODE",
            code
        };
    }

    #findMatchLength(endLength, startLength, cursor, decoratorChar) {
        if (endLength === startLength) {
            return startLength;
        }

        for (let i = cursor + 1; i < this.#tokens.length; i++) {
            const token = this.#tokens[i];

            const exists = is(token);
            const isEnd = token.type === "DECORATION_END" || token.type === "DECORATION";
            const isSameStyle = token.literal[0] === decoratorChar;
            const isSameLength = token.literal.length === startLength;

            if (exists && isEnd && isSameStyle && isSameLength) {
                // Exact match later
                return null;
            }
        }

        // Go with it
        return startLength;
    }

    /**
     * @return {MarkDownTextNode | MarkDownDecorationNode}
     */
    #parseDecoration() {
        const startToken = this.#tokens[this.#tokenCursor];
        if (startToken.type === "DECORATION" && startToken.literal === '_') {
            this.#advance();
            return {
                type: "TEXT",
                text: '_'
            };
        }

        const decoratorChar = startToken.literal[0]; // '*' or '_'
        const startLength = startToken.literal.length;

        let cursor = this.#tokenCursor + 1;
        let tmp = 0;

        while (cursor < this.#tokens.length) {
            if (tmp++ > 1000) {
                console.log(this.#tokens, this.#tokenCursor);
                throw new Error("max iter reached (parse decor)");
            }

            const token = this.#tokens[cursor];

            // Stop at block boundary
            if (this.#isBlock(token.type)) {
                break;
            }

            const isEnd = token.type === "DECORATION_END"
                || token.type === "DECORATION";
            const isSameStyle = decoratorChar === token.literal[0]
            if (!isEnd || !isSameStyle) {
                cursor++;
                continue;
            }

            const endLength = token.literal.length;
            // reject if end is too weak
            if (endLength < startLength) {
                cursor++;
                continue;
            }

            const matchLength = this.#findMatchLength(endLength, startLength, cursor, decoratorChar);
            if (!is(matchLength)) {
                cursor++;
                continue;
            }

            // Determine decoration type
            let style;
            if (matchLength >= 3) {
                style = "ITALIC-BOLD";
            } else if (matchLength === 2) {
                style = "BOLD";
            } else {
                style = "ITALIC";
            }

            // Split start if needed
            if (startLength > matchLength) {
                this.#tokens[this.#tokenCursor] = {
                    type: startToken.type,
                    literal: decoratorChar.repeat(startLength - matchLength)
                };
            }

            // Split end if needed
            if (endLength > matchLength) {
                this.#tokens[cursor] = {
                    type: token.type,
                    literal: decoratorChar.repeat(endLength - matchLength)
                };
            }

            // Extract inner this.#tokens
            const innerTokens = this.#tokens
                .slice(this.#tokenCursor + 1, cursor)
                .filter(x => is(x));

            this.#advance((cursor - this.#tokenCursor) + 1);

            const parser = new MarkDownAstParser(false);
            const ast = parser.createAst(innerTokens);

            return {
                type: "DECORATION",
                style,
                ast
            };
        }

        this.#advance();
        return {
            type: "TEXT",
            text: startToken.literal
        };
    }



    /** @type {MarkDownAstNode[]} */
    #ast;

    /**
     * @param {MarkDownAstNode} astNode
     */
    #addNode(astNode) {
        const last = this.#ast.at(-1);
        if (!is(last)) {
            this.#ast.push(astNode);
            return;
        }

        if (astNode.type === "TEXT" && last.type === "TEXT") {
            last.text += astNode.text;
            return;
        }

        if (astNode.type === "PARAGRAPH" && last.type === "NEW_LINE") {
            this.#ast.pop();
            this.#addNode(astNode);
            return;
        }

        if (astNode.type === "NEW_LINE") {
            switch (last.type) {
                case "TEXT": {
                    last.text += ' ';
                    return;
                }

                case "PARAGRAPH":
                    return;
            }
        }

        this.#ast.push(astNode);
    }



    /**
     * @param {MarkDownToken[]} tokens
     * @return {MarkDownAstNode[]}
     */
    createAst(tokens) {
        console.log('create_ast:', tokens.map(x => x.literal + '[' + x.type + ']').join(''));
        this.#tokens = tokens;
        this.#tokenCursor = 0;
        this.#ast = [];

        let tmp = 0;
        while (this.#tokenCursor < this.#tokens.length) {
            const t = this.#tokens[this.#tokenCursor];

            if (tmp++ > 1000) {
                console.log(this.#tokens, this.#tokenCursor, this.#ast);
                throw new Error("max iter reached");
            }

            if (!is(this.#tokens[this.#tokenCursor])) {
                this.#advance();
                continue;
            }

            if (this.#current("NEW_LINE")) {
                this.#addNode({ type: "NEW_LINE", text: ' ' });
                this.#advance();
                continue;
            }

            if (this.#allowBlockParsing) {
                if (this.#current("PARAGRAPH")) {
                    const paragraph = this.#parseParagraph();
                    if (is(paragraph)) {
                        this.#addNode(paragraph);
                    }

                    continue;
                }

                if (this.#isBlockNext("HEADING")) {
                    this.#addNode(this.#parseHeading());
                    continue;
                }

                if (this.#isBlockNext("LIST_ITEM")) {
                    this.#addNode(this.#parseList());
                    continue;
                }

                if (this.#isBlockNext("CODE_BLOCK")) {
                    this.#addNode(this.#parseCodeBlock());
                    continue;
                }

                if (this.#isBlockNext("QUOTE_BLOCK")) {
                    this.#addNode(this.#parseQuote());
                    continue;
                }
            }

            if (this.#current("EXCLAMATION")) {
                this.#addNode(this.#parseImage());
                continue;
            }

            if (this.#current("CODE")) {
                this.#addNode(this.#parseCode());
                continue;
            }

            const isDecoration = this.#current("DECORATION")
                || this.#current("DECORATION_START");
            if (isDecoration) {
                this.#addNode(this.#parseDecoration());
                continue;
            }

            this.#addNode({ type: "TEXT", text: this.#tokens[this.#tokenCursor].literal });
            this.#advance();
        }

        return this.#ast;
    }
}



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

                case "CODE": {
                    parent.append(
                        jsml.code('md-code', node.code)
                    );
                    break;
                }

                case "CODE_BLOCK": {
                    parent.append(
                        jsml.div('md-code-block', node.code)
                    );
                    break;
                }

                case "DECORATION": {
                    let decoration = jsml.span('md-decoration ' + node.style);
                    let container = decoration;

                    switch (node.style) {
                        case "BOLD": {
                            container = decoration = jsml.b('md-bold');
                            break;
                        }

                        case "ITALIC": {
                            container = decoration = jsml.i('md-italic');
                            break;
                        }

                        case "ITALIC-BOLD": {
                            container = jsml.i('md-italic');
                            decoration = jsml.b('md-bold', container);
                            break;
                        }

                        default: break;
                    }

                    parent.append(decoration);
                    MarkDown.astToHtml(container, node.ast);
                    break;
                }

                case "QUOTE": {
                    const quote = jsml.div({
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
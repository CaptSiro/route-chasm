class MarkDownTokenizer {
    /** @type {MarkDownToken[]} */
    #tokens;
    /** @type {number} */
    #position;
    /** @type {string} */
    #text;

    #escapable = ['\\', "-", "#", ">", "<", "*", "_", "`", "!", "[", "]", "(", ")", '"'];



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

    #extractRepetition(chars, line, position) {
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
     * @param {string} char
     * @param {string} line
     * @param {MarkDownTokenType} tokenType
     * @param {boolean | null} followedByWhitespace
     */
    #readRepetition(char, line, tokenType, followedByWhitespace = true) {
        const literal = this.#extractRepetition(char, line, this.#position);

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
                    case "\\": {
                        const next = line[this.#position + 1];

                        if (is(next)) {
                            if (!this.#escapable.includes(next)) {
                                this.#appendText(char);
                            } else {
                                this.#position++;
                            }

                            this.#appendText(next);
                            break;
                        }

                        this.#position++;
                        break;
                    }

                    case "-": {
                        const literal = this.#extractRepetition(char, line, this.#position);
                        if (literal.length <= 2) {
                            this.#appendText(literal);
                            break;
                        }

                        this.#pushToken({ type: "HORIZONTAL_LINE", literal });
                        break;
                    }
                    case "#": {
                        this.#readRepetition(char, line, "HEADING");
                        break;
                    }
                    case ">": {
                        this.#readRepetition(char, line, "QUOTE_BLOCK", null);
                        break;
                    }
                    case "<": {
                        const jump1 = line[this.#position + 1];
                        const jump2 = line[this.#position + 2];

                        if (jump1 === '>') {
                            this.#pushToken({ type: "HTML_START", literal: '<>' });
                            break;
                        }

                        if (jump1 === '/' && jump2 === ">") {
                            this.#pushToken({ type: "HTML_END", literal: '</>' });
                            break;
                        }

                        this.#appendText(char);
                        break;
                    }

                    case "*": {
                        const literal = this.#extractRepetition('*', line, this.#position);
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
                        const literal = this.#extractRepetition('_', line, this.#position);
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
                        const literal = this.#extractRepetition(char, line, this.#position);
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
                    case '"': {
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
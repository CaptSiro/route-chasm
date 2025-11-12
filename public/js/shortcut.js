class Shortcut {
    /** @type {Shortcut[]} */
    static #shortcuts = [];

    /**
     * @param {EventTarget} target
     */
    static bind(target) {
        target.addEventListener("keydown", event => {
            for (const shortcut of Shortcut.#shortcuts) {
                shortcut.perform(event);
            }
        });
    }

    /**
     * @param {(event: KeyboardEvent|Event) => void} action
     * @param {string} expression
     */
    static register(action, expression) {
        const tokens = expression
            .toLowerCase()
            .split('+')
            .map(x => x.trim());

        let key, shift, control, alt;
        shift = control = alt = false;

        for (const token of tokens) {
            switch (token) {
                case 'shift':
                    shift = true;
                    break;
                case 'ctrl':
                    control = true;
                    break;
                case 'alt':
                    alt = true;
                    break;
                default:
                    key = token;
                    break;
            }
        }

        if (!is(key)) {
            throw new Error(`Invalid shortcut. The expression '${expression}' does not contain key`);
        }

        const shortcut = new Shortcut(action, key, shift, control, alt);
        shortcut.bind();
    }



    /** @type {(event: KeyboardEvent|Event) => void} */
    #action;
    /** @type {string} */
    #key;
    /** @type {boolean} */
    #shift;
    /** @type {boolean} */
    #control;
    /** @type {boolean} */
    #alt;

    /** @type {boolean} */
    #bound = false;

    /**
     * @param {(event: KeyboardEvent|Event) => void} action
     * @param {string} key
     * @param {boolean} shift
     * @param {boolean} control
     * @param {boolean} alt
     */
    constructor(action, key, shift = false, control = false, alt = false) {
        this.#action = action;
        this.#key = key.toLowerCase();
        this.#shift = shift;
        this.#control = control;
        this.#alt = alt;

        switch (key) {
            case 'esc':
                this.#key = 'escape';
                break;
            case 'space':
                this.#key = ' ';
                break;
        }
    }



    /**
     * @param {KeyboardEvent|Event} event
     * @return {boolean}
     */
    matches(event) {
        const keyMatches = event.key.toLowerCase() === this.#key;
        const modifiersMatches = event.shiftKey === this.#shift
            && event.ctrlKey === this.#control
            && event.altKey === this.#alt;

        return keyMatches && modifiersMatches;
    }

    /**
     * @param {KeyboardEvent|Event} event
     */
    perform(event) {
        if (this.matches(event)) {
            this.#action(event);
        }
    }

    bind() {
        if (!this.#bound) {
            Shortcut.#shortcuts.push(this);
        }

        this.#bound = true;
    }
}

Shortcut.bind(window);

/**
 * @template T
 * @typedef {(value: T) => any} ImpulseListener
 *
 * @template T
 * @typedef {{ pulseOnDuplicate?: boolean, default?: T }} ImpulseOptions
 */

/** @template I */
class Impulse {
    /** @type {I | undefined} */
    #lastValue;
    /** @type {ImpulseOptions<I> | undefined} */
    #options;
    /** @type {ImpulseListener<I>[]} */
    #listeners;



    /**
     * @param {ImpulseOptions<I> | undefined} options
     */
    constructor(options) {
        this.#options = options
        this.#listeners = [];
        this.#lastValue = options?.default;
    }



    /**
     * @param {boolean} bool
     * @return {void}
     */
    setPulseOnDuplicate(bool) {
        if (this.#options === undefined) {
            this.#options = {};
        }

        this.#options.pulseOnDuplicate = bool;
    }

    /**
     * @param {ImpulseListener<I>} listener
     * @returns {Impulse<I>}
     */
    listen(listener) {
        this.#listeners.push(listener);
        return this;
    }

    /**
     * @param {ImpulseListener<I>} listener
     */
    removeListener(listener) {
        const i = this.#listeners.indexOf(listener);

        if (i === -1) {
            return;
        }

        this.#listeners.splice(i, 1);
    }

    /**
     * @param {I} value
     */
    pulse(value) {
        if (this.#options?.pulseOnDuplicate === false && value === this.lastValue) {
            return;
        }

        this.lastValue = value;

        for (let i = 0; i < this.#listeners.length; i++) {
            this.#listeners[i](value);
        }
    }

    /**
     * @returns {I | undefined}
     */
    value() {
        return this.lastValue;
    }
}

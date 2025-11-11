/**
 * @template T
 */
class Binding {
    /**
     * @template V
     * @param {V[]} values
     * @returns {BindingValidator<?>}
     */
    static setValidator(values) {
        return value => values.includes(value) ? null : `Value '${value}' is not recognised`;
    }



    /** @type {T|null} */
    #value;
    /** @type {BindingValidator<T>} */
    #validator;
    /** @type {BindingOnChange<T>[]} */
    #listeners;

    /**
     * @param {T|null} value
     * @param {BindingValidator<T>} validator
     */
    constructor(value, validator) {
        this.#value = value;
        this.#validator = validator;
        this.#listeners = [];
    }

    /**
     * @param {BindingOnChange<T>} listener
     */
    onChange(listener) {
        this.#listeners.push(listener);
        return this;
    }

    /**
     * @return {T|null}
     */
    get() {
        return this.#value;
    }

    /**
     * @param {T} value
     * @param {HTMLElement} context
     * @return {Promise<string|null>}
     */
    async set(value, context) {
        const error = await this.#validator(value, context);
        if (is(error)) {
            return error;
        }

        this.#value = value;
        for (const listener of this.#listeners) {
            listener(value, context);
        }

        return null;
    }
}
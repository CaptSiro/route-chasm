class NumberRange {
    /**
     * @param {string} charA
     * @param {string} charB
     * @return {NumberRange}
     */
    static from(charA, charB) {
        return new NumberRange(charA.charCodeAt(0), charB.charCodeAt(0));
    }



    /** @type {number} */
    #start;
    /** @type {number} */
    #end;



    /**
     * @param {number} start
     * @param {number} end
     */
    constructor(start, end) {
        this.#start = start;
        this.#end = end;
    }



    /**
     * @param {number} x
     * @returns {boolean}
     */
    includes(x) {
        return this.#start <= x && x <= this.#end;
    }

    /**
     * @param {string} x
     * @returns {boolean}
     */
    includesChar(x) {
        return this.includes(x.charCodeAt(0));
    }
}



const uppercase = NumberRange.from('A', 'Z');
const lowercase = NumberRange.from('a', 'z');
const numbers = NumberRange.from('0', '9');
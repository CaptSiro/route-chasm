/**
 * @param {HTMLElement} element
 */
function phraseTranslator_init(element) {
    const translateUrl = element.dataset.url;
    if (!is(translateUrl)) {
        element.remove();
        return;
    }

    element.addEventListener("click", async () => {
        const response = await fetch(translateUrl);
        if (response.status >= 300) {
            await window_alert(await response.text());
        }

        location.reload();
    });
}

/**
 * @param {HTMLElement} element
 */
function phraseEditor_removeTranslationButton(element) {
    const accumulator = element.dataset.accumulator;
    const id = element.dataset.id;
    const control = element.dataset.control;
    if (!is(id) || !is(control)) {
        return;
    }

    element.addEventListener('click', () => {
        if (is(accumulator)) {
            const form = form_get(element);

            /** @type {HTMLInputElement} */
            const field = form_getControl(form, accumulator);
            if (!is(field)) {
                return;
            }

            const deleted = is(field.value) && field.value !== ""
                ? field.value.split(',')
                : [];

            deleted.push(Number(id));
            field.value = deleted.join(',');
        }

        const translationControl = element.closest('.' + control);
        if (!is(translationControl)) {
            return;
        }

        translationControl.remove();
    });
}
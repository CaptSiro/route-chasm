/**
 * @typedef {{ body: any, type: string }} Payload
 */

/**
 * @param {HTMLElement} form
 * @param {{ message: string, property?: string }} error
 */
async function form_showError(form, error) {
    const message = error['message'];
    if (!is(message)) {
        return;
    }

    const property = error['property'];
    const input = $(`[name=${property}]`, form);
    if (!is(property) || !is(input)) {
        await window_alert(message, WINDOW_ALERT_SETTINGS);
        return;
    }

    input.classList.add("form-invalid");
    const errorView = jsml.span("form-invalid-message", message);
    input.after(errorView);
}

/**
 * @param {HTMLElement} form
 */
async function form_submit(form) {
    /** @type {(HTMLElement) => Payload} */
    const transformer = std_getFunction(form.dataset.transformer ?? '');
    if (!is(transformer)) {
        throw new Error("Mandatory form attribute 'data-transformer' was not set. " + form);
    }

    const payload = transformer(form);
    const headers = {
        'X-Request-Type': payload.type,
        'X-Response-Type': 'application/json'
    };

    for (const errorView of $$('.form-invalid-message', form)) {
        errorView.remove();
    }

    for (const input of $$('.form-invalid', form)) {
        input.classList.remove('form-invalid');
    }

    const response = await fetch(window.location, {
        method: form.dataset.method,
        headers,
        body: payload.body
    });

    if (response.status >= 400) {
        /** @type {any} */
        const result = await response.json();
        if (is(result)) {
            await form_showError(form, result);
            return false;
        }

        if (Array.isArray(result['group'])) {
            for (const error of result['group']) {
                await form_showError(form, error);
            }

            return false;
        }

        if (is(result['message'])) {
            await window_alert(result['message'], WINDOW_ALERT_SETTINGS);
        }

        return false;
    }

    if (response.headers.has('X-Next')) {
        window.location.replace(response.headers.get('X-Next'));
        return false;
    }

    console.log(await response.text());
    return false;
}



/**
 * @param {HTMLElement} control
 */
function form_extract(control) {
    const extract = std_getFunction(control.dataset.extract);
    if (is(extract)) {
        return extract(control);
    }

    if ('value' in control) {
        return control.value;
    }

    return null;
}

/**
 * @param {HTMLElement} form
 * @returns {Payload}
 */
function form_formData(form) {
    const data = new FormData();

    for (const control of form.querySelectorAll("[name]")) {
        if (Boolean(control.dataset.skipSubmit)) {
            continue;
        }

        if (control.type === "file") {
            for (const file of control.files) {
                data.append(control.name, file);
            }

            continue;
        }

        if (control.type === "checkbox") {
            data.append(control.name, control.checked);
            continue;
        }

        data.append(control.name, form_extract(control));
    }

    return {
        body: data,
        type: 'application/x-www-form-urlencoded'
    };
}

/**
 * @param {HTMLElement} form
 * @returns {Payload}
 */
function form_json(form) {
    const json = {};

    for (const control of form.querySelectorAll("[name]")) {
        if (Boolean(control.dataset.skipSubmit)) {
            continue;
        }

        if (control.type === "checkbox") {
            json[control.name] = control.checked;
            continue;
        }

        json[control.name] = form_extract(control);
    }

    return {
        body: JSON.stringify(json),
        type: 'application/json'
    };
}



/**
 * @param {HTMLElement} container
 */
function form_file(container) {
    const input = container.querySelector('input')
    const loadedFiles = container.querySelector('.form-files');

    loadedFiles.addEventListener('click', event => {
        event.preventDefault();
    });

    const showFiles = () => {
        loadedFiles.textContent = "";

        for (const file of input.files) {
            loadedFiles.append(Tag(file.name, true, (tag, event) => {
                const name = tag.querySelector('span').textContent;
                const transfer = new DataTransfer();

                for (const f of input.files) {
                    if (f.name !== name) {
                        transfer.items.add(f);
                    }
                }

                input.files = transfer.files;
                event.preventDefault();
            }));
        }
    }

    container.addEventListener('drop', event => {
        input.files = event.dataTransfer.files;
        showFiles();
        event.preventDefault();
    });

    container.addEventListener('dragover', event => {
        event.preventDefault();
    });

    container.querySelectorAll('& > *').forEach(e => {
        e.addEventListener('drop', event => {
            event.preventDefault();
        });
    });

    input.addEventListener('change', () => {
        showFiles();
    });

    for (const file of loadedFiles.children) {
        const remove = file.querySelector('button');
        remove.addEventListener('click', event => {
            file.remove();
            event.preventDefault();
        });
    }
}



/**
 * @param {HTMLElement} container
 */
function form_password(container) {
    const control = $('.password-visibility-control', container);
    if (!is(control)) {
        return;
    }

    const field = $('input', container);
    const hide = $('[data-action="hide"]', control);
    const show = $('[data-action="show"]', control);

    hide.addEventListener('click', () => {
        field.type = 'password';
        hide.classList.add('hide');
        show.classList.remove('hide');
    });

    show.addEventListener('click', () => {
        field.type = 'text';
        show.classList.add('hide');
        hide.classList.remove('hide');
    });
}



/**
 * @param {HTMLSelectElement} select
 * @returns {Map<string, HTMLOptionElement>}
 */
function form_select_getOptions(select) {
    const options = new Map();

    for (const option of select.children) {
        if (option instanceof HTMLOptionElement) {
            options.set(option.value, option);
        }
    }

    return options;
}

/**
 * @param {HTMLElement} select
 * @param {string} query
 */
function form_select_search(select, query) {
    const q = query.toLowerCase();
    let first = undefined;

    for (const option of $$(".dropdown-item", select)) {
        const valid = option.textContent.toLowerCase().includes(q);
        option.classList.toggle('hide', !valid);
        option.classList.remove('selected');

        if (valid && !is(first)) {
            first = option;
        }
    }

    first?.classList.add('selected');
}

/**
 * @param {HTMLElement} current
 * @return {boolean}
 */
function form_select_skipPredicate(current) {
    return current.classList.contains('hide');
}

/**
 * @param {HTMLElement} container
 */
function form_select(container) {
    const select = $('select', container);
    const options = form_select_getOptions(select);
    const searchInput = $('.select-search', container);
    const search = $('.search', container);
    const selection = $('.selection', container);
    const selectionLabel = $('.option', selection);
    const dropdown = $('.dropdown', container);
    const dropdownItems = $('.dropdown-items', container);

    searchInput?.addEventListener('focus', () => {
        search.classList.remove('opacity-0');
        selection.classList.add('opacity-0');
        dropdown_expand(container, dropdown);

        setTimeout(() => {
            const selected = $('.selected', dropdownItems);
            if (!is(selected)) {
                return;
            }

            std_dom_scrollIntoView(selected, dropdown);
        }, DROPDOWN_ANIMATION_DURATION);
    });

    searchInput?.addEventListener('blur', () => {
        search.classList.add('opacity-0');
        selection.classList.remove('opacity-0');
        dropdown_shrink(container, dropdown);
        searchInput.value = '';

        for (const option of $$(".dropdown-item", dropdownItems)) {
            option.classList.remove('hide');
        }
    });

    const searchFunction = std_getFunction(container.dataset.search) ?? form_select_search;
    searchInput?.addEventListener('input', () => {
        searchFunction(container, searchInput.value);
    });

    const selectOption = value => {
        const option = options.get(value);
        searchInput.blur();

        if (!is(option)) {
            return;
        }

        select.value = option.value;
        selectionLabel.textContent = option.textContent;
    };

    searchInput?.addEventListener('keydown', event => {
        if (event.key === "ArrowUp" || event.key === "ArrowDown") {
            event.preventDefault();

            const selected = $('.selected', dropdownItems);
            if (!is(selected)) {
                dropdownItems.children[0]?.classList.add('selected');
                return;
            }

            selected.classList.remove('selected');
            let target;

            if (event.key === "ArrowUp") {
                target = std_dom_findChild(selected, std_dom_previousChild, form_select_skipPredicate);
            }

            if (event.key === "ArrowDown") {
                target = std_dom_findChild(selected, std_dom_nextChild, form_select_skipPredicate);
            }

            if (!is(target)) {
                return;
            }

            target.classList.add('selected');
            std_dom_scrollIntoView(target, dropdown);
        }

        if (event.key === "Enter") {
            event.preventDefault();

            const selected = $('.selected', dropdownItems);
            selectOption(selected.dataset.value);
        }
    });

    dropdownItems.addEventListener('pointerdown', event => {
        const item = event instanceof Element && event.target.classList.contains('dropdown-item')
            ? event.target
            : event.target.closest('.dropdown-item');

        if (!is(item)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        $('.selected', dropdownItems)?.classList.remove('selected');
        item.classList.add('selected');
        selectOption(item.dataset.value);
    });

    dropdown_shrink(container, dropdown);
    dropdown.classList.remove('hide');
    dropdown_animate(dropdown, true);
}



/**
 * @param {HTMLElement} current
 * @return {boolean}
 */
function form_multiSelect_skipPredicate(current) {
    return form_select_skipPredicate(current)
        || current.classList.contains('selected');
}

/**
 * @param {string} value
 * @param {string} label
 * @return {HTMLDivElement}
 */
function form_multiSelect_Option(value, label) {
    return jsml.div({ class: "option", "data-value": value }, [
        jsml.span(_, label),
        jsml.button(
            { type: "button", "x-init": form_multiSelect_removeOptionButton.name },
            Icon('nf-fa-close', 'X')
        )
    ]);
}

/**
 * @param {HTMLElement} container
 * @param {string} value
 */
function form_multiSelect_selectOption(container, value) {
    const option = $(`select option[value=${value}]`, container);
    if (!is(option)) {
        return;
    }

    option.setAttribute("selected", "selected");

    const dropdownItem = $(`.dropdown-item[data-value=${value}]`, container);
    dropdownItem?.classList.add('selected');

    const options = $('.options-selected', container);
    options.append(form_multiSelect_Option(value, dropdownItem.textContent));
}

/**
 * @param {HTMLElement} container
 * @param {string} value
 */
function form_multiSelect_deselelectOption(container, value) {
    const option = $(`select option[value=${value}]`, container);
    if (!is(option)) {
        return;
    }

    option.removeAttribute('selected');
    const dropdownItem = $(`.dropdown-item[data-value=${value}]`, container);
    dropdownItem?.classList.remove('selected');
}

/**
 * @param {HTMLElement} select
 * @param {string} query
 */
function form_multiSelect_search(select, query) {
    const q = query.toLowerCase();
    let first = undefined;

    for (const option of $$(".dropdown-item", select)) {
        const valid = option.textContent.toLowerCase().includes(q);
        option.classList.toggle('hide', !valid);
        option.classList.remove('cursor');

        if (valid && !is(first)) {
            first = option;
        }
    }

    first?.classList.add('cursor');
}

/**
 * @param {HTMLElement} container
 */
function form_multiSelect_init(container) {
    const select = $('select', container);
    const options = form_select_getOptions(select);
    const searchInput = $('.select-search', container);
    const search = $('.search', container);
    const selection = $('.selection', container);
    const dropdown = $('.dropdown', container);
    const dropdownItems = $('.dropdown-items', container);

    searchInput?.addEventListener('focus', () => {
        search.classList.remove('opacity-0');
        selection.classList.add('opacity-0');
        dropdown_expand(container, dropdown);

        setTimeout(() => {
            const selected = $('.selected', dropdownItems);
            if (!is(selected)) {
                return;
            }

            std_dom_scrollIntoView(selected, dropdown);
        }, DROPDOWN_ANIMATION_DURATION);
    });

    searchInput?.addEventListener('blur', () => {
        search.classList.add('opacity-0');
        selection.classList.remove('opacity-0');
        dropdown_shrink(container, dropdown);
        searchInput.value = '';

        for (const option of $$(".dropdown-item", dropdownItems)) {
            option.classList.remove('hide');
        }
    });

    const searchFunction = std_getFunction(container.dataset.search) ?? form_multiSelect_search;
    searchInput?.addEventListener('input', () => {
        searchFunction(container, searchInput.value);
    });

    searchInput?.addEventListener('keydown', event => {
        if (event.key === "ArrowUp" || event.key === "ArrowDown") {
            event.preventDefault();

            const selected = $('.cursor', dropdownItems);
            if (!is(selected)) {
                dropdownItems.children[0]?.classList.add('cursor');
                return;
            }

            selected.classList.remove('cursor');
            let target;

            if (event.key === "ArrowUp") {
                target = std_dom_findChild(selected, std_dom_previousChild, form_multiSelect_skipPredicate);
            }

            if (event.key === "ArrowDown") {
                target = std_dom_findChild(selected, std_dom_nextChild, form_multiSelect_skipPredicate);
            }

            if (!is(target)) {
                return;
            }

            target.classList.add('cursor');
            std_dom_scrollIntoView(target, dropdown);
        }

        if (event.key === "Enter") {
            event.preventDefault();

            const cursor = $('.cursor', dropdownItems);
            form_multiSelect_selectOption(container, cursor.dataset.value);
            cursor.classList.remove('cursor');

            const target = std_dom_findChild(cursor, std_dom_nextChild, form_multiSelect_skipPredicate);
            if (!is(target)) {
                return;
            }

            target.classList.add('cursor');
            std_dom_scrollIntoView(target, dropdown);
        }
    });

    dropdownItems.addEventListener('pointerdown', event => {
        event.preventDefault();
        event.stopImmediatePropagation();

        const item = event instanceof Element && event.target.classList.contains('dropdown-item')
            ? event.target
            : event.target.closest('.dropdown-item');

        if (!is(item)) {
            return;
        }

        form_multiSelect_selectOption(container, item.dataset.value);
    });

    dropdown_shrink(container, dropdown);
    dropdown.classList.remove('hide');
    dropdown_animate(dropdown, true);
}

/**
 * @param {HTMLElement} control
 */
function form_multiSelect_extract(control) {
    if (!(control instanceof HTMLSelectElement)) {
        return;
    }

    let selected = '';
    let first = true;

    for (const option of control.children) {
        if (!option.selected) {
            continue;
        }

        if (!first) {
            selected += ';';
        }

        selected += option.value;
        first = false;
    }

    return selected;
}

/**
 * @param {HTMLElement} button
 */
function form_multiSelect_removeOptionButton(button) {
    const option = button.closest('.option');
    if (!is(option)) {
        console.warn("Cannot initialize option remove button because the button is not inside '.option' element");
        return;
    }

    const removeOption = () => {
        form_multiSelect_deselelectOption(option.closest('.form-select'), option.dataset.value);
        option.remove();
    }

    button.addEventListener('click', removeOption);
    option.addEventListener('auxclick', removeOption);
}

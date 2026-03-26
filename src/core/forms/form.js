/**
 * @typedef {{ body: any, type: string }} Payload
 */

/**
 * @param {HTMLElement} form
 */
function form_init(form) {
    form.addEventListener('submit', async event => {
        await form_submit(form, event);
    });

    const formBarrier = $('.form-barrier', form);
    const cancelSubmit = event => {
        if (event.target instanceof Element) {
            const button = event.target.closest('button');
            if (!is(button)) {
                return;
            }

            const buttonType = button.getAttribute('type');
            if (buttonType === 'submit' || buttonType === 'reset') {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
        }
    };

    formBarrier.addEventListener('click', cancelSubmit);
    formBarrier.addEventListener('pointerdown', cancelSubmit);
    formBarrier.addEventListener('mousedown', cancelSubmit);
}

/**
 * @param {HTMLElement} control
 * @returns {HTMLFormElement | null}
 */
function form_get(control) {
    return control.closest('form');
}

/**
 * @param {HTMLElement | null} form
 * @param {string} name
 * @returns {HTMLElement | null}
 */
function form_getControl(form, name) {
    if (!is(form)) {
        return null;
    }

    return form.querySelector(`[name=${name}]`);
}

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
    const input = $(`[name="${property}"]`, form);
    if (!is(property) || !is(input)) {
        await window_alert(message, WINDOW_ALERT_SETTINGS);
        return;
    }

    const fn = std_getFunction(input.dataset.onError) ?? form_onError;
    await fn(input, form, message);
}

/**
 * @param {HTMLElement} input
 * @param {HTMLFormElement} form
 * @param {string} message
 */
function form_onError(input, form, message) {
    input.classList.add("form-invalid");
    const errorView = jsml.span("form-invalid-message auto-delete", message);
    input.after(errorView);
}

/**
 * @param {HTMLElement} form
 * @param {SubmitEvent} event
 */
async function form_submit(form, event) {
    event.preventDefault();
    event.stopImmediatePropagation();

    const w = window_createNotice("Submitting form...", { isDialog: true });
    window_open(w);

    /** @type {(form: HTMLElement, event: SubmitEvent) => Payload} */
    const transformer = std_getFunction(form.dataset.transformer ?? '');
    if (!is(transformer)) {
        throw new Error("Mandatory form attribute 'data-transformer' was not set. " + form);
    }

    const payload = transformer(form, event);
    const headers = {
        'X-Request-Type': payload.type,
        'X-Response-Type': 'application/json'
    };

    for (const errorView of $$('.auto-delete', form)) {
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

    window_close(w);

    if (await std_fetch_handleServerError(response)) {
        return;
    }

    if (response.status >= 400) {
        const fn = std_getFunction(form.dataset.onSubmitFailure) ?? form_onSubmitFailure;
        await fn(form, response);
        return;
    }

    std_fetch_follow(response);

    const fn = std_getFunction(form.dataset.onSubmitSuccess);
    if (is(fn)) {
        fn(form, response);
    }
}

/**
 * @param {HTMLFormElement} form
 * @param {Response} response
 * @param {string} redirect
 */
function form_onRedirect(form, response, redirect) {
    location.assign(redirect);
}

/**
 * @param {HTMLFormElement} form
 * @param {Response} response
 * @return {Promise<void>}
 */
async function form_onSubmitFailure(form, response) {
    /** @type {any} */
    const result = await response.json();
    if (is(result)) {
        await form_showError(form, result);
        return;
    }

    if (Array.isArray(result['group'])) {
        for (const error of result['group']) {
            await form_showError(form, error);
        }

        return;
    }

    if (is(result['message'])) {
        await window_alert(result['message'], WINDOW_ALERT_SETTINGS);
    }
}



/**
 * @param {HTMLElement} control
 */
function form_extract(control) {
    const extract = std_getFunction(control.dataset.extract);
    if (is(extract)) {
        return extract(control, control.dataset);
    }

    if ('value' in control) {
        return control.value;
    }

    return null;
}

/**
 * @param {HTMLElement} element
 */
function form_extractContentEditable(element) {
    return std_dom_getWhitespaceTextContent(element);
}

/**
 * @param {HTMLElement} form
 * @param {SubmitEvent} event
 * @returns {Payload}
 */
function form_formData(form, event) {
    const data = new FormData();

    if (event.submitter.hasAttribute('name')) {
        data.append(
            event.submitter.getAttribute('name'),
            event.submitter.getAttribute('value') ?? ''
        );
    }

    for (const control of form.querySelectorAll("[name]")) {
        if (Boolean(control.dataset.skipSubmit) || control.type === 'submit') {
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

        data.append(control.getAttribute('name'), form_extract(control));
    }

    return {
        body: data,
        type: 'application/x-www-form-urlencoded'
    };
}

/**
 * @param json
 * @param {Set<string>} arrays
 * @param {string} name
 * @param value
 */
function form_jsonAppend(json, arrays, name, value) {
    if (arrays.has(name)) {
        json[name].push(value);
        return;
    }

    const x = json[name];
    if (is(x)) {
        json[name] = [x, value];
        arrays.add(name);
        return;
    }

    json[name] = value;
}

/**
 * @param {HTMLElement} form
 * @param {SubmitEvent} event
 * @returns {Payload}
 */
function form_json(form, event) {
    const json = {};
    const arrays = new Set();

    if (event.submitter.hasAttribute('name')) {
        form_jsonAppend(
            json,
            arrays,
            event.submitter.getAttribute('name'),
            event.submitter.getAttribute('value') ?? ''
        );
    }

    for (const control of form.querySelectorAll("[name]")) {
        if (Boolean(control.dataset.skipSubmit) || control.type === "submit") {
            continue;
        }

        if (control.type === "checkbox") {
            form_jsonAppend(json, arrays, control.name, control.checked);
            continue;
        }

        form_jsonAppend(json, arrays, control.name, form_extract(control));
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
 * @param {HTMLElement} input
 * @param {HTMLFormElement} form
 * @param {string} message
 */
function form_password_onError(input, form, message) {
    const password = input.closest(".form-control.password");
    if (!is(password)) {
        return window_alert(message, WINDOW_ALERT_SETTINGS);
    }

    password.append(
        jsml.span("form-invalid-message auto-delete", message)
    );
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
 * @param {HTMLElement} container
 */
function form_select_clearOptions(container) {
    const select = $('select', container);
    const items = $('.dropdown-items', container);
    if (!is(select) || !is(items)) {
        return;
    }

    select.textContent = '';
    items.textContent = '';
}



/**
 * @param {string} label
 * @param {string} value
 * @return {HTMLDivElement}
 */
function form_select_Option(label, value) {
    return jsml.div({
        class: 'dropdown-item',
        'data-value': value,
    }, label);
}

/**
 * @param {HTMLElement} container
 * @param {string} label
 * @param {string} value
 */
function form_select_addOption(container, label, value) {
    const select = $('select', container);
    const items = $('.dropdown-items', container);
    if (!is(select) || !is(items)) {
        return;
    }

    select.append(new Option(label, value));
    items.append(form_select_Option(label, value));
}

function form_select_showOptions(container) {
    dropdown_expand(container, $('.dropdown', container));
}

function form_select_hideOptions(container) {
    dropdown_shrink(container, $('.dropdown', container));
}

/**
 * @param {HTMLElement} container
 * @param {string} value
 */
function form_select_selectOption(container, value) {
    let option = $(`select option[value="${value}"]`, container);
    if (!is(option)) {
        /** @type {HTMLElement} */
        const select = $("select", container);
        for (const o of select.children) {
            if (o instanceof HTMLOptionElement && o.value === value) {
                option = o;
                break;
            }
        }

        if (!is(option)) {
            return;
        }
    }

    option.parentElement.value = option.value;
    option.parentElement.dispatchEvent(new Event('change', { bubbles: true }));

    const label = $('.option', container);
    if (is(label)) {
        label.textContent = option.textContent;
    }

    $('.select-search', container)?.blur();
    $('.dropdown-item.cursor', container)?.classList.remove('cursor');

    const dropdownItem = $(`.dropdown-item[data-value="${value}"]`, container);
    dropdownItem?.classList.add('cursor');
}

/**
 * @param {HTMLElement} container
 * @param {string} query
 */
function form_select_search(container, query) {
    const q = query.toLowerCase();
    let first = undefined;

    for (const option of $$(".dropdown-item", container)) {
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
 * @param {string} query
 * @return {Promise<void>}
 */
async function form_select_searchAsync(container, query) {
    const minLength = container.dataset.searchMinLength ?? 3;
    if (query.length < minLength) {
        form_select_clearOptions(container);
        return;
    }

    const href = container.dataset.searchUrl;
    if (!is(href)) {
        return;
    }

    const url = new URL(href);
    url.searchParams.set(container.dataset.searchQueryArgument ?? 'q', query);

    const response = await fetch(std_jsonEndpoint(url));
    if (await std_fetch_handleServerError(response)) {
        return;
    }

    if (!response.ok) {
        console.error("Cannot load options: " + response.statusText);
        return;
    }

    form_select_clearOptions(container);
    for (const { label, value } of await response.json()) {
        form_select_addOption(container, label, value);
    }

    form_select_showOptions(container);
}

/**
 * @param {HTMLElement} container
 * @param {HTMLInputElement} searchInput
 * @param {HTMLElement} search
 * @param {HTMLElement} selection
 * @param {HTMLElement} dropdown
 * @param {HTMLElement} dropdownItems
 * @param {Function} defaultSearchFunction
 * @private
 */
function form_select_searchInputInit(
    container, searchInput, search, selection, dropdown, dropdownItems, defaultSearchFunction
) {
    searchInput?.addEventListener('focus', () => {
        search.classList.remove('opacity-0');
        selection.classList.add('opacity-0');
        dropdown_expand(container, dropdown);

        setTimeout(() => {
            const selected = $('.cursor', dropdownItems);
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

    const searchFunction = std_getFunction(container.dataset.search) ?? defaultSearchFunction;
    searchInput?.addEventListener('input', () => {
        searchFunction(container, searchInput.value);
    });
}

/**
 * @param {HTMLElement} dropdown
 * @param {HTMLElement} dropdownItems
 * @param {"up" | "down"} direction
 * @param {SkipPredicate<HTMLElement>} skipPredicate
 */
function form_select_moveCursor(dropdown, dropdownItems, direction, skipPredicate) {
    const cursor = $('.cursor', dropdownItems);
    if (!is(cursor)) {
        if (dropdownItems.children.length === 0) {
            return;
        }

        const target = skipPredicate(dropdownItems.children[0])
            ? std_dom_findChild(dropdownItems.children[0], std_dom_nextChild, skipPredicate)
            : dropdownItems.children[0];

        target?.classList.add('cursor');
        return;
    }

    cursor.classList.remove('cursor');
    let target;

    if (direction === "up") {
        target = std_dom_findChild(cursor, std_dom_previousChild, skipPredicate);
    } else if (direction === "down") {
        target = std_dom_findChild(cursor, std_dom_nextChild, skipPredicate);
    }

    if (!is(target)) {
        return;
    }

    target.classList.add('cursor');
    std_dom_scrollIntoView(target, dropdown);
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
function form_select_init(container) {
    const searchInput = $('.select-search', container);
    const search = $('.search', container);
    const selection = $('.selection', container);
    const dropdown = $('.dropdown', container);
    const dropdownItems = $('.dropdown-items', container);

    form_select_searchInputInit(
        container, searchInput, search, selection, dropdown, dropdownItems, form_select_search
    );

    searchInput?.addEventListener('keydown', event => {
        if (event.key === "Escape") {
            searchInput.blur();
            return;
        }

        if (event.key === "ArrowUp" || event.key === "ArrowDown") {
            event.preventDefault();

            if (event.key === "ArrowUp") {
                form_select_moveCursor(dropdown, dropdownItems, "up", form_select_skipPredicate);
            }

            if (event.key === "ArrowDown") {
                form_select_moveCursor(dropdown, dropdownItems, "down", form_select_skipPredicate);
            }
        }

        if (event.key === "Enter") {
            event.preventDefault();

            const cursor = $('.cursor', dropdownItems);
            if (!is(cursor) || form_select_skipPredicate(cursor)) {
                return;
            }

            form_select_selectOption(container, cursor.dataset.value);
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

        form_select_selectOption(container, item.dataset.value);
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
    const option = $(`select option[value="${value}"]`, container);
    if (!is(option)) {
        return;
    }

    option.setAttribute("selected", "selected");

    const dropdownItem = $(`.dropdown-item[data-value="${value}"]`, container);
    dropdownItem?.classList.add('selected');

    const options = $('.options-selected', container);
    options.append(form_multiSelect_Option(value, dropdownItem.textContent));
}

/**
 * @param {HTMLElement} container
 * @param {string} value
 */
function form_multiSelect_deselelectOption(container, value) {
    const option = $(`select option[value="${value}"]`, container);
    if (!is(option)) {
        return;
    }

    option.removeAttribute('selected');
    const dropdownItem = $(`.dropdown-item[data-value="${value}"]`, container);
    dropdownItem?.classList.remove('selected');
}

/**
 * @param {HTMLElement} container
 */
function form_multiSelect_init(container) {
    const searchInput = $('.select-search', container);
    const search = $('.search', container);
    const selection = $('.selection', container);
    const dropdown = $('.dropdown', container);
    const dropdownItems = $('.dropdown-items', container);

    form_select_searchInputInit(
        container, searchInput, search, selection, dropdown, dropdownItems, form_select_search
    );

    searchInput?.addEventListener('keydown', event => {
        if (event.key === "Escape") {
            searchInput.blur();
            return;
        }

        if (event.key === "ArrowUp" || event.key === "ArrowDown") {
            event.preventDefault();

            if (event.key === "ArrowUp") {
                form_select_moveCursor(dropdown, dropdownItems, "up", form_multiSelect_skipPredicate);
            }

            if (event.key === "ArrowDown") {
                form_select_moveCursor(dropdown, dropdownItems, "down", form_multiSelect_skipPredicate);
            }
        }

        if (event.key === "Enter") {
            event.preventDefault();

            const cursor = $('.cursor', dropdownItems);
            if (!is(cursor) || form_multiSelect_skipPredicate(cursor)) {
                return;
            }

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

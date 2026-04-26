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

    const action = form.getAttribute('action')
        ?? window.location;

    const response = await fetch(action, std_fetch_json({
        method: form.dataset.method,
        headers,
        body: payload.body
    }));

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

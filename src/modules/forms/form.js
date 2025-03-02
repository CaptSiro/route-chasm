/**
 * @typedef {{ body: any, type: string }} Payload
 */

/**
 * @param {HTMLElement} form
 */
async function form_submit(form) {
    /** @type {(HTMLElement) => Payload} */
    const transformer = call_getFunction(form.dataset.transformer ?? '');
    if (transformer === undefined) {
        throw new Error("Mandatory form attribute 'data-transformer' was not set. " + form);
    }

    const payload = transformer(form);
    const headers = {
        'X-Request-Type': payload.type,
        'X-Response-Type': 'application/json'
    };

    const response = await fetch(window.location, {
        method: form.dataset.method,
        headers,
        body: payload.body
    });

    if (!response.ok) {
        /** @type {any} */
        const error = await response.json();
        if (error['property'] === undefined) {
            await window_alert(error.message);
            return;
        }

        await window_alert(error.message);
        return;
    }

    if (response.headers.has('Location')) {
        window.location.replace(response.headers.get('Location'));
        return;
    }

    console.log(await response.json());
}



/**
 * @param {HTMLElement} form
 * @returns {Payload}
 */
function form_formData(form) {
    const data = new FormData();

    for (const input of form.querySelectorAll("[name]")) {
        if (input.type === "file") {
            for (const file of input.files) {
                data.append(input.name, file);
            }

            continue;
        }

        if (input.type === "checkbox") {
            data.append(input.name, input.checked);
            continue;
        }

        data.append(input.name, input.value);
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

    for (const input of form.querySelectorAll("[name]")) {
        if (input.type === "checkbox") {
            json[input.name] = input.checked;
            continue;
        }

        json[input.name] = input.value;
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
    if (control === null) {
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
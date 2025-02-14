/**
 * @param {HTMLElement} form
 */
async function submitForm(form) {
    const data = new FormData();
    const json = {};

    for (const input of form.querySelectorAll("[name]")) {
        if (input.type === "file") {
            for (const file of input.files) {
                data.append(input.name, file);
            }

            continue;
        }

        data.append(input.name, input.value);
        json[input.name] = input.value;
    }

    const response = await fetch(window.location, {
        method: form.dataset.method,
        body: data
    });

    console.log(await response.text());

    const jsonResponse = await fetch(window.location, {
        method: form.dataset.method,
        body: JSON.stringify(json),
    });

    console.log(await jsonResponse.text());
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
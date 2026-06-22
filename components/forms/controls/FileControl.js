
/**
 * @param {HTMLElement} element
 */
function fileControl_getElements(element) {
    const input = $('input[type="file"]', element);
    const filesElement = $('.files', element);

    return std_elements({ input, filesElement });
}

/**
 * @param {HTMLInputElement} input
 * @param {FileList} files
 */
function fileControl_setFiles(input, files) {
    input.files = files;
    input.dispatchEvent(new Event("change", {
        bubbles: true
    }));
}

/**
 * @param {HTMLElement} element
 */
function fileControl(element) {
    const { input, filesElement } = fileControl_getElements(element);

    /** @param {FileList} files */
    const setFiles = files => fileControl_setFiles(input, files);

    input.addEventListener('change', event => {
        filesElement.textContent = '';

        if (input.files.length === 0) {
            filesElement.classList.add('display-none');
            return;
        }

        filesElement.classList.remove('display-none');

        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];
            const tag = Tag(file.name, true, () => {
                const transfer = new DataTransfer();

                for (let j = 0; j < input.files.length; j++) {
                    if (j === i) {
                        continue;
                    }

                    transfer.items.add(input.files[j]);
                }

                setFiles(transfer.files);
            });

            tag.addEventListener('click', event => {
                event.stopPropagation();
                event.preventDefault();
                return false;
            });

            filesElement.append(tag);
        }
    });

    const dragStart = event => {
        event.preventDefault();
        event.stopPropagation();

        element.classList.add('drag-over');
    };

    const dragEnd = event => {
        event.preventDefault();
        event.stopPropagation();

        element.classList.remove('drag-over');
    };

    element.addEventListener('dragenter', dragStart);
    element.addEventListener('dragover', dragStart);

    element.addEventListener('dragleave', dragEnd);
    element.addEventListener('dragend', dragEnd);
    element.addEventListener('drop', dragEnd);

    element.addEventListener('drop', event => {
        const files = event.dataTransfer.files;

        if (files.length <= 0) {
            return;
        }

        setFiles(files);
    });
}

/**
 * @param {HTMLElement} element
 */
function fileControl_clear(element) {
    const { input } = fileControl_getElements(element);

    fileControl_setFiles(input, new DataTransfer().files);
}
/**
 * @param {DataTransfer} dataTransfer
 * @return {boolean}
 */
function fs_hasFiles(dataTransfer) {
    for (const item of dataTransfer.items) {
        if (item.kind === "file") {
            return true;
        }
    }

    return false;
}

/**
 * @param {DataTransfer} dataTransfer
 * @return {number}
 */
function fs_countFiles(dataTransfer) {
    let i = 0;

    for (const item of dataTransfer.items) {
        if (item.kind === "file") {
            i++;
        }
    }

    return i;
}

/**
 * @param {HTMLElement} element
 */
function fs_dropArea_init(element) {
    const { directory, accept, upload } = element.dataset;
    const dropSingle = $(".drop-overlay-single", element);
    const dropMultiple = $(".drop-overlay-multiple", element);
    const uploadOverlay = $(".upload-overlay", element);

    const isDropArea = x => x.classList.contains("fs-drop-area");

    const drop = event => {
        if (!fs_hasFiles(event.dataTransfer)) {
            return;
        }

        dropMultiple.classList.remove("display");
        dropMultiple.classList.remove("display");

        event.preventDefault();

        const formData = new FormData();
        for (const file of event.dataTransfer.files) {
            formData.append('file', file);
        }

        const progress = new Impulse();
        const w = window_fileUpload(progress);
        window_open(w);

        const request = new XMLHttpRequest();
        request.upload.addEventListener('progress', event => {
            progress.pulse(event.loaded / event.total);
        });

        // request.upload.addEventListener('load', () => location.reload());

        request.open('post', upload);
        request.timeout = 45000;
        request.send(formData);
    };

    let timeout = null;
    const hide = () => {
        dropMultiple.classList.remove("display");
        dropMultiple.classList.remove("display");
        element.classList.remove("overlay");

        if (is(timeout)) {
            clearTimeout(timeout);
        }

        timeout = null;
    }

    element.addEventListener("drop", drop);
    window.addEventListener("drop", (event) => {
        if (!fs_hasFiles(event.dataTransfer)) {
            return;
        }

        event.preventDefault();
    });

    window.addEventListener("dragover", event => {
        if (!fs_hasFiles(event.dataTransfer)) {
            return;
        }

        event.preventDefault();

        const target = event.target;
        if (!std_dom_isDescendant(target, isDropArea)) {
            hide();
            return;
        }

        if (fs_countFiles(event.dataTransfer) > 1) {
            dropMultiple.classList.add("display");
            dropSingle.classList.remove("display");
        } else {
            dropSingle.classList.add("display");
            dropMultiple.classList.remove("display");
        }

        element.classList.add("overlay");

        if (is(timeout)) {
            clearTimeout(timeout);
        }

        timeout = setTimeout(() => {
            hide();
            timeout = null;
        }, 1000);
    });

    window.addEventListener("dragleave", event => {
        if (!fs_hasFiles(event.dataTransfer)) {
            return;
        }

        if (std_dom_isDescendant(event.target, isDropArea)) {
            return;
        }

        hide();
    });
}
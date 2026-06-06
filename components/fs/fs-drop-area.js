const FS_RELOAD_CURRENT_DIRECTORY = 'fsReloadCurrentDirectory';

window.addEventListener(FS_RELOAD_CURRENT_DIRECTORY, () => {
    location.reload();
});



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

    const drop = async event => {
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

        const response = await fetch(std_jsonEndpoint(upload, 'out'), {
            method: "post",
            body: formData
        });

        hide();
        window_close(w);
        if (await std_fetch_handleServerError(response)) {
            return;
        }

        if (!response.ok) {
            try {
                const message = (await response.json()).message;
                await window_alert(`File upload failed: '${message}'`);
            } catch (e) {
                await window_alert(`File upload failed`);
            }

            return;
        }

        std_fetch_follow(response);
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
/**
 * @param {HTMLElement} element
 * @param {string} url
 * @param {string} controlId
 * @param {?string} fileType
 */
function form_fs_fileControl(element, { url, controlId, fileType }) {
    const control = $("#" + controlId);
    if (!is(control)) {
        return;
    }

    const previewImage = $('.preview-image', element);
    const previewFile = $('.preview-file', element);
    const previewNone = $('.preview-none', element);

    $('.select', element).addEventListener("click", async () => {
        const api = api_loadFileSystem();
        if (!is(api)) {
            console.warn('Could not load File System API');
            return;
        }

        const hash = await window_fileSelect(api.createDirectoryUrl(fileType));
        if (!is(hash)) {
            return;
        }

        control.value = hash;

        if (hash.length === 0) {
            previewImage.classList.add('display-none');
            previewFile.classList.add('display-none');
            previewNone.classList.remove('display-none');
            return;
        }

        const response = await fetch(std_jsonEndpoint(api.createInfoUrl(hash)));
        if (await std_fetch_handleServerError(response)) {
            return;
        }

        if (!response.ok) {
            console.warn('Could not fetch information about selected file. ' + response.statusText);
            return;
        }

        previewNone.classList.add('display-none');

        /** @type {FileInfo} */
        const info = await response.json();
        if (info.type.startsWith('image')) {
            previewImage.classList.remove('display-none');
            previewFile.classList.add('display-none');

            const img = $('img', previewImage);
            img.src = api.createFileUrl(hash, 'img:file-image-preview');
            return;
        }

        previewImage.classList.add('display-none');
        previewFile.classList.remove('display-none');

        $('.icon', previewFile).innerHTML = info.icon;
        $('.name', previewFile).textContent = info.fileName;
        $('.size', previewFile).textContent = info.sizeHumanReadable;
    });
}
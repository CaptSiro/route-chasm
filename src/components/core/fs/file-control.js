/**
 * @param {HTMLElement} element
 * @param {string} url
 * @param {string} controlId
 */
function form_fs_fileControl(element, { url, controlId }) {
    const control = $("#" + controlId);
    if (!is(control)) {
        return;
    }

    element.addEventListener("click", async () => {
        const file = await window_fileSelect(url);
        if (!is(file)) {
            return;
        }

        control.value = file;
    });
}
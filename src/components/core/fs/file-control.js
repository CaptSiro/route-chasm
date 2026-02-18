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
        control.value = await window_fileSelect(control.value, url);
        console.log(control.value);
    });
}
function articleEditor_success() {
    return window_alert('Article was saved successfully');
}

/**
 * @param {HTMLElement} element
 */
function articleEditor_cancel(element) {
    element.addEventListener('click', () => {
        window.close();
    });
}
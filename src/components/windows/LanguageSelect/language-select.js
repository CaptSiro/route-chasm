/**
 * @param {HTMLElement} element
 */
function languageSelect_languageButton(element) {
    element.addEventListener('click', () => {
        const url = new URL(window.location.href);

        if (url.searchParams.get("l") === element.dataset.code) {
            window_issueClose(element);
            return;
        }

        url.searchParams.set("l", element.dataset.code);
        window.location.replace(url);
    });
}
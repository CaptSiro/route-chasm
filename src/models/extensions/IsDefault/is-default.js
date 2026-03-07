/**
 * @param {HTMLElement} element
 * @param {string} url
 */
function isDefault_toggle(element, { url }) {
    if (!(element instanceof HTMLInputElement)) {
        return;
    }

    element.addEventListener('change', async () => {
        if (!element.checked) {
            return;
        }

        const response = await fetch(url);
        if (await std_fetch_handleServerError(response)) {
            return;
        }

        if (!response.ok) {
            console.warn(response.statusText);
            return;
        }

        if (response.headers.has('X-Reload')) {
            location.reload();
        }
    });
}
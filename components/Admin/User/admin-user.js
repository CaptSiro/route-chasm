/**
 * @param {HTMLElement} element
 * @param {string} url
 */
function admin_user_loginAsUser(element, { url }) {
    element.addEventListener('click', async () => {
        const response = await fetch(url);

        if (await std_fetch_handleServerError(response)) {
            return;
        }

        if (!response.ok) {
            await window_alert(response.statusText);
            return;
        }

        window.location.reload();
    });
}
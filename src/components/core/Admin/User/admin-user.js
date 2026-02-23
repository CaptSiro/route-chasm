/**
 * @param {HTMLElement} element
 * @param {string} url
 */
function admin_user_loginAsUser(element, { url }) {
    element.addEventListener('click', async () => {
        const response = await fetch(url);

        if (!response.ok) {
            await window_alert(response.statusText);
            return;
        }

        window.location.reload();
    });
}
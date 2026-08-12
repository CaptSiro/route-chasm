/**
 * @param {HTMLElement} button
 * @param {string} url
 * @param {string} id
 */
function nexus_deleteButton(button, { url, id }) {
    if (!is(url)) {
        button.remove()
        return;
    }

    button.addEventListener('click', async () => {
        const message = is(id)
            ? `Do you want to delete '${id}'?`
            : "Do you want to delete the record?"

        if (!(await window_confirm(message, WINDOW_CONFIRM_SETTINGS))) {
            return;
        }

        const response = await fetch(url, std_fetch_json({ method: 'DELETE' }));
        if (await std_fetch_handleServerError(response)) {
            return;
        }

        if (response.status >= 400) {
            const result = response.json();
            await window_alert(
                result['message'] ?? 'Error has occurred while processing your request.',
                WINDOW_ALERT_SETTINGS
            );
            return;
        }

        button.parentElement.remove();
    });
}



/**
 * @param {HTMLElement} button
 */
function nexus_cancelButton(button) {
    button.addEventListener('click', () => {
        const url = button.dataset.url;
        if (!is(url)) {
            return;
        }

        window.location.replace(url);
    });
}
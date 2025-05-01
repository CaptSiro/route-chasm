/**
 * @param {HTMLElement} button
 */
function nexus_deleteButton(button) {
    const url = button.dataset.url;
    if (!is(url)) {
        button.remove()
        return;
    }

    button.addEventListener('click', async () => {
        if (!(await window_confirm("Do you want to delete the record?", WINDOW_CONFIRM_SETTINGS))) {
            return;
        }

        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-Response-Type': 'application/json'
            }
        });

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
/**
 * @param {HTMLElement} button
 */
function nexus_deleteButton(button) {
    const url = button.dataset.url;
    if (url === undefined) {
        button.remove()
        return;
    }

    button.addEventListener('click', async () => {
        if (!(await window_confirm("Do you want to delete the record?"))) {
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
            await window_alert(result['message'] ?? 'Error has occurred while processing your request.')
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
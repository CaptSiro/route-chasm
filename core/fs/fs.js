/**
 * @param {HTMLElement} element
 */
function fs_renameButton_init(element) {
    const { url, id } = element.dataset;
    element.addEventListener("click", async () => {
        const name = await window_prompt('Rename entry');
        if (!is(name)) {
            return;
        }

        const response = await fetch(std_jsonEndpoint(url), {
            method: 'patch',
            body: JSON.stringify({ id, name })
        });

        if (await std_fetch_handleServerError(response)) {
            return;
        }

        if (!response.ok) {
            await window_alert((await response.json())?.message ?? "Unknown error");
            return;
        }

        location.reload();
    });
}



/**
 * @param {HTMLElement} element
 */
function fs_deleteButton_init(element) {
    const { url, id } = element.dataset;
    element.addEventListener("click", async () => {
        const response = await fetch(std_jsonEndpoint(url), {
            method: 'delete',
            body: JSON.stringify({ id, name }),
        });

        if (await std_fetch_handleServerError(response)) {
            return;
        }

        if (!response.ok) {
            await window_alert((await response.json())?.message ?? "Unknown error");
            return;
        }

        location.reload();
    });
}
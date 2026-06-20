/**
 * @param {HTMLElement} button
 * @param {string} mkdir
 */
function fs_mkdirButton_init(button, { mkdir }) {
    let opened = false;

    const parentId = button.closest('.window')?.id;

    button.addEventListener('click', async () => {
        if (opened) {
            return;
        }

        const settings = WINDOW_ALERT_SETTINGS;
        if (is(parentId) && parentId !== '') {
            settings.parent = parentId;
        }

        opened = true;
        const name = await window_prompt('Name for new directory', settings);
        opened = false;

        if (!is(name)) {
            return;
        }

        const response = await fetch(std_jsonEndpoint(mkdir), {
            method: 'post',
            body: JSON.stringify({ name })
        });

        if (await std_fetch_handleServerError(response)) {
            return;
        }

        if (!response.ok) {
            await window_alert(await response.text());
            return;
        }

        location.reload();
    });
}
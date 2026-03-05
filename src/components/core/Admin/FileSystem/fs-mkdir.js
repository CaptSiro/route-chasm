/**
 * @param {HTMLElement} element
 * @param {string} mkdir
 */
function fs_mkdirButton_init(element, { mkdir }) {
    element.addEventListener('click', async () => {
        const name = await window_prompt('Name for new directory');
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
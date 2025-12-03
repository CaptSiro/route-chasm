/**
 * @param {HTMLElement} element
 */
function fs_mkdirButton_init(element) {
    const mkdir = element.dataset.mkdir;

    element.addEventListener('click', async () => {
        const name = await window_prompt('Name for new directory');
        if (!is(name)) {
            return;
        }

        const url = new URL(mkdir);
        url.searchParams.set('i', 'json');
        url.searchParams.set('o', 'json');

        const response = await fetch(mkdir, {
            method: 'post',
            body: JSON.stringify({ name })
        });

        if (!response.ok) {
            await window_alert(await response.text());
            return;
        }

        location.reload();
    });
}
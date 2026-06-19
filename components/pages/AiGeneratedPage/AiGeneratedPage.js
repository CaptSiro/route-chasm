/**
 * @param {HTMLElement} element
 * @param {string} target
 */
function aiPage_source(element, { target }) {
    const targetElement = $(target);
    if (!is(targetElement)) {
        return;
    }

    element.addEventListener('input', () => {
        targetElement.innerHTML = element.textContent;
    });
}

function aiPage_toggleEditMode() {
    $('.ai-generated-page')?.classList.toggle('edit');
}

/**
 * @param {HTMLElement} element
 * @param {string} fileContentHtml
 * @param {string} fileContentCss
 * @param {string} fileContentJs
 */
function aiPage_save(element, { fileContentHtml, fileContentCss, fileContentJs }) {
    const html = $(fileContentHtml);
    const css = $(fileContentCss);
    const js = $(fileContentJs);

    if (!is(html)) {
        return;
    }

    element.addEventListener('click', async () => {
        const w = window_createNotice("Saving source files...", { isDialog: true });
        window_open(w);

        const body = { html: html.textContent };

        if (is(css)) {
            body.css = css.textContent;
        }

        if (is(js)) {
            body.js = js.textContent;
        }

        const response = await fetch(std_jsonEndpoint(location.href), {
            method: "put",
            body: JSON.stringify(body)
        });

        window_close(w);

        if (await std_fetch_handleServerError(response)) {
            return;
        }

        if (!response.ok) {
            await window_alert("Source files could not be saved");
            return;
        }

        location.reload();
    });
}
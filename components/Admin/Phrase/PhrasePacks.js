/**
 * @param {HTMLElement} form
 * @param {Response} response
 */
async function translationPacks_onExportSuccess(form, response) {
    const blob = await response.blob();
    const disposition = response.headers.get('Content-Disposition');
    if (!is(disposition)) {
        return;
    }

    std_download(blob, disposition.substring('attachment; filename='.length));
}

/**
 * @param {HTMLElement} form
 * @param {Response} response
 */
function translationPacks_onImportSuccess(form, response) {
    const NAME_IMPORT_FILES = 'importFiles';
    const control = $(`.form-file-control[data-name='${NAME_IMPORT_FILES}']`, form);
    if (!is(control)) {
        return;
    }

    fileControl_clear(control);
}
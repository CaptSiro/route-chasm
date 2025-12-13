/**
 * @param {Element} form
 * @param {Response} response
 */
async function aiPageGenerator_success(form, response) {
    await window_alert('Generation successful');
}

/**
 * @param {Element} form
 * @param {Response} response
 */
async function aiPageGenerator_failure(form, response) {
    await window_alert('Generation failed (unknown reason)');
}


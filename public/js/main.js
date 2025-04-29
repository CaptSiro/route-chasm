/**
 * @param {boolean} isOnline
 * @private
 */
function _internetStatus(isOnline) {
    document.body.classList.toggle('online', isOnline);
    document.body.classList.toggle('offline', !isOnline);
}

window.addEventListener('load', () => {
    window.addEventListener('online', () => _internetStatus(true));
    window.addEventListener('offline', () => _internetStatus(false));

    _internetStatus(window.navigator.onLine);
});

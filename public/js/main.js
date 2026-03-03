/** @type {WindowSettings} */
const WINDOW_ALERT_SETTINGS = {
    width: '400px',
    isMinimizable: true,
    isDraggable: true,
    isResizable: false,
}

/** @type {WindowSettings} */
const WINDOW_CONFIRM_SETTINGS = {
    width: '400px',
    isMinimizable: true,
    isDraggable: true,
    isResizable: false
}



window.addEventListener('load', () => {
    const setInternetStatus = isOnline => {
        document.body.classList.toggle('online', isOnline);
        document.body.classList.toggle('offline', !isOnline);
    }

    window.addEventListener('online', () => setInternetStatus(true));
    window.addEventListener('offline', () => setInternetStatus(false));

    setInternetStatus(window.navigator.onLine);
});

window.addEventListener('load', () => {
    const setInternetStatus = isOnline => {
        document.body.classList.toggle('online', isOnline);
        document.body.classList.toggle('offline', !isOnline);
    }

    window.addEventListener('online', () => setInternetStatus(true));
    window.addEventListener('offline', () => setInternetStatus(false));

    setInternetStatus(window.navigator.onLine);
});

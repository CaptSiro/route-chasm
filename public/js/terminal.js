const KEY_TERMINAL_OPENED = 'ls_to';

window.addEventListener('load', () => {
    const terminal = document.querySelector('.terminal');
    if (terminal === null) {
        return;
    }

    const show = Boolean(localStorage.getItem(KEY_TERMINAL_OPENED) ?? true);
    if (!show) {
        terminal.classList.add('hide');
    }

    window.addEventListener('keydown', event => {
        if (event.key !== "F1") {
            return;
        }

        event.preventDefault();
        terminal.classList.toggle("hide");
    });
});

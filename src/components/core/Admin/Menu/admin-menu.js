/**
 * @param {HTMLElement} element
 */
function adminMenu(element) {
    for (const item of $$(".has-sub-menu", element)) {
        const menu = $('.sub-menu', item);
        const hide = $('.dropdown-control [data-action=hide]');
        const show = $('.dropdown-control [data-action=show]');

        $('.head', item)?.addEventListener('click', event => {
            menu.classList.toggle('hide');
            show.classList.toggle('hide');
            hide.classList.toggle('hide');
        });
    }

    for (const item of $$(".menu-item", element)) {
        const a = $('a', $('.head', item));
        if (a === null) {
            continue;
        }

        item.addEventListener('click', () => {
            a.click();
        });
    }
}

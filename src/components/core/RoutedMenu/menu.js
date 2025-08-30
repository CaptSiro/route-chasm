/**
 * @param {HTMLElement} element
 */
function menu_init(element) {
    if (is(element.parentElement.closest('.menu'))) {
        return;
    }

    for (const item of $$(".has-sub-menu", element)) {
        const menu = $('.menu', item);
        const hide = $('.dropdown-control [data-action=hide]', item);
        const show = $('.dropdown-control [data-action=show]', item);

        $('.head', item)?.addEventListener('click', event => {
            menu.classList.toggle('hide');
            show.classList.toggle('hide');
            hide.classList.toggle('hide');

            if (event.target.closest('.dropdown-control') !== null) {
                event.stopImmediatePropagation();
                event.preventDefault();
            }
        });
    }

    for (const item of $$(".menu-item", element)) {
        const a = $('a', $('.head', item));
        if (!is(a)) {
            continue;
        }

        item.addEventListener('click', event => {
            if (event.target.closest('.dropdown-control') !== null) {
                return;
            }

            event.stopImmediatePropagation();
            a.click();
        });
    }
}

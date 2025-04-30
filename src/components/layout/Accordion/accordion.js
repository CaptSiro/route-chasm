/**
 * @param {HTMLElement} element
 */
function accordion_init(element) {
    const title = $('.title', element);
    const content = $('.content', element);

    title.addEventListener('click', () => {
        element.classList.toggle('expanded');
        dropdown_toggleExpand(element, content);
    });

    if (Boolean(element.dataset.isExpanded)) {
        dropdown_expand(element, content);
    }

    dropdown_animate(element, true);
}

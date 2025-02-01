function accordion_expand(accordion, content) {
    if (!accordion.classList.contains('expanded')) {
        content.style.maxHeight = '0';
        return;
    }

    content.style.maxHeight = (content.scrollHeight + 1) + "px";
}

/**
 * @param {HTMLElement} element
 */
function accordion(element) {
    const title = $('.title', element);
    const content = $('.content', element);

    title.addEventListener('click', () => {
        element.classList.toggle('expanded');
        accordion_expand(element, content);
    });

    if (Boolean(element.dataset.isExpanded)) {
        element.classList.add('expanded');
        accordion_expand(element, content);
    }
}

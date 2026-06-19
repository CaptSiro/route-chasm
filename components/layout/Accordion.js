function accordion_toggleExpand(element, content) {
    if (!dropdown_isExpanded(element)) {
        if (is(element.dataset.timeout)) {
            clearTimeout(Number(element.dataset.timeout));
        }

        element.dataset.timeout = String(setTimeout(() => {
            delete element.dataset.timeout;
            content.classList.add('visible');
        }, DROPDOWN_ANIMATION_DURATION));
    } else {
        content.classList.remove('visible');
    }

    dropdown_toggleExpand(element, content);
}

/**
 * @param {HTMLElement} element
 */
function accordion_init(element) {
    const title = $('.title', element);
    const content = $('.content', element);

    title.addEventListener('click', () => {
        accordion_toggleExpand(element, content);
    });

    const observer = new ResizeObserver(entries => {
        dropdown_refit(element, content);
    });

    const detector = $('.content-resize-detector', element);
    if (is(detector)) {
        observer.observe(detector, { box: "border-box" });
    }

    if (Boolean(element.dataset.isExpanded)) {
        accordion_toggleExpand(element, content);
    }

    dropdown_animate(element, true);
}

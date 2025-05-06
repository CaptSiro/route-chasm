function dropdown_isExpanded(container) {
    return container.classList.contains('expanded');
}

const DROPDOWN_ANIMATION_DURATION = 250;

function dropdown_animate(container, useAnimations) {
    container.classList.toggle('animate', useAnimations);
}

function dropdown_expand(container, content) {
    container.classList.add('expanded');
    const maxHeight = content.dataset.maxHeight;
    if (!is(maxHeight)) {
        content.style.maxHeight = content.scrollHeight + 1 + "px";
        return;
    }

    content.style.maxHeight = `min(${content.scrollHeight + 1}px, ${maxHeight})`;
}

function dropdown_refit(container, content) {
    if (!dropdown_isExpanded(container)) {
        return;
    }

    const maxHeight = content.dataset.maxHeight;
    if (!is(maxHeight)) {
        content.style.maxHeight = content.scrollHeight + 1 + "px";
        return;
    }

    content.style.maxHeight = `min(${content.scrollHeight + 1}px, ${maxHeight})`;
}

function dropdown_shrink(container, content) {
    container.classList.remove('expanded');
    content.style.maxHeight = '0';
}

function dropdown_toggleExpand(container, content) {
    if (dropdown_isExpanded(container)) {
        dropdown_shrink(container, content);
        return;
    }

    dropdown_expand(container, content);
}

const DROPDOWN_IF_NOT_EXPANDED = expanded => !expanded;
const DROPDOWN_IF_EXPANDED = expanded => expanded;

/**
 * @param container
 * @param content
 * @param doHide
 */
function dropdown_init(container, content, doHide = DROPDOWN_IF_NOT_EXPANDED) {
    if (doHide()) {
        container.classList.add("expanded");
        dropdown_toggleExpand(container, content);
    }
}
/**
 * @param {HTMLElement} current
 * @return {boolean}
 */
function form_multiSelect_skipPredicate(current) {
    return form_select_skipPredicate(current)
        || current.classList.contains('selected');
}

/**
 * @param {string} value
 * @param {string} label
 * @return {HTMLDivElement}
 */
function form_multiSelect_Option(value, label) {
    return jsml.div({ class: "option", "data-value": value }, [
        jsml.span(_, label),
        jsml.button(
            { type: "button", "x-init": form_multiSelect_removeOptionButton.name },
            Icon('nf-fa-close', 'X')
        )
    ]);
}

/**
 * @param {HTMLElement} container
 * @param {HTMLOptionElement} option
 */
function form_multiSelect_onOptionSelected(container, option) {
    if (option.selected) {
        return;
    }

    option.selected = true;

    const dropdownItem = $(`.dropdown-item[data-value="${option.value}"]`, container);
    dropdownItem?.classList.add('selected');

    const options = $('.options-selected', container);
    options.append(form_multiSelect_Option(option.value, option.textContent));
}

/**
 * @param {HTMLElement} container
 * @param {HTMLOptionElement} option
 */
function form_asyncMultiSelect_onOptionSelected(container, option) {
    option.dataset.eternal = 'true';
    form_multiSelect_onOptionSelected(container, option);
}

/**
 * @param {HTMLElement} container
 * @param {HTMLOptionElement} option
 */
function form_multiSelect_onOptionDeselected(container, option) {
    option.selected = false;
    const dropdownItem = $(`.dropdown-item[data-value="${option.value}"]`, container);
    dropdownItem?.classList.remove('selected');
}

/**
 * @param {HTMLElement} container
 * @param {HTMLOptionElement} option
 */
function form_asyncMultiSelect_onOptionDeselected(container, option) {
    delete option.dataset.eternal;
    form_multiSelect_onOptionDeselected(container, option);
}

/**
 * @param {HTMLElement} container
 * @param {string} value
 */
function form_multiSelect_deselelectOption(container, value) {
    const option = form_select_findOption(container, value);
    if (!is(option)) {
        return;
    }

    const fn = std_getFunction(container.dataset.onOptionDeselected)
        ?? form_multiSelect_onOptionDeselected;

    fn(container, option);
}

/**
 * @param {HTMLElement} container
 */
function form_multiSelect_init(container) {
    const searchInput = $('.select-search', container);
    const search = $('.search', container);
    const selection = $('.selection', container);
    const dropdown = $('.dropdown', container);
    const dropdownItems = $('.dropdown-items', container);

    form_select_searchInputInit(
        container, searchInput, search, selection, dropdown, dropdownItems, form_select_search
    );

    searchInput?.addEventListener('keydown', event => {
        if (event.key === "Escape") {
            searchInput.blur();
            return;
        }

        if (event.key === "ArrowUp" || event.key === "ArrowDown") {
            event.preventDefault();

            if (event.key === "ArrowUp") {
                form_select_moveCursor(dropdown, dropdownItems, "up", form_multiSelect_skipPredicate);
            }

            if (event.key === "ArrowDown") {
                form_select_moveCursor(dropdown, dropdownItems, "down", form_multiSelect_skipPredicate);
            }
        }

        if (event.key === "Enter") {
            event.preventDefault();

            const cursor = $('.cursor', dropdownItems);
            if (!is(cursor) || form_multiSelect_skipPredicate(cursor)) {
                return;
            }

            form_select_selectOption(container, cursor.dataset.value, form_multiSelect_onOptionSelected);
            cursor.classList.remove('cursor');

            const target = std_dom_findChild(cursor, std_dom_nextChild, form_multiSelect_skipPredicate);
            if (!is(target)) {
                return;
            }

            target.classList.add('cursor');
            std_dom_scrollIntoView(target, dropdown);
        }
    });

    dropdownItems.addEventListener('pointerdown', event => {
        event.preventDefault();
        event.stopImmediatePropagation();

        const item = event instanceof Element && event.target.classList.contains('dropdown-item')
            ? event.target
            : event.target.closest('.dropdown-item');

        if (!is(item)) {
            return;
        }

        form_select_selectOption(container, item.dataset.value, form_multiSelect_onOptionSelected);
    });

    dropdown_shrink(container, dropdown);
    dropdown.classList.remove('hide');
    dropdown_animate(dropdown, true);
}

/**
 * @param {HTMLElement} control
 */
function form_multiSelect_extract(control) {
    if (!(control instanceof HTMLSelectElement)) {
        return;
    }

    let selected = '';
    let first = true;

    for (const option of control.children) {
        if (!option.selected) {
            continue;
        }

        if (!first) {
            selected += ';';
        }

        selected += option.value;
        first = false;
    }

    return selected;
}

/**
 * @param {HTMLElement} button
 */
function form_multiSelect_removeOptionButton(button) {
    const option = button.closest('.option');
    if (!is(option)) {
        console.warn("Cannot initialize option remove button because the button is not inside '.option' element");
        return;
    }

    const removeOption = () => {
        form_multiSelect_deselelectOption(option.closest('.form-select'), option.dataset.value);
        option.remove();
    }

    button.addEventListener('click', removeOption);
    option.addEventListener('auxclick', removeOption);
}
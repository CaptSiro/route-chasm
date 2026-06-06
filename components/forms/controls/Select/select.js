/**
 * @param {HTMLSelectElement} select
 * @returns {Map<string, HTMLOptionElement>}
 */
function form_select_getOptions(select) {
    const options = new Map();

    for (const option of select.children) {
        if (option instanceof HTMLOptionElement) {
            options.set(option.value, option);
        }
    }

    return options;
}

/**
 * @param {HTMLElement} container
 */
function form_select_clearOptions(container) {
    const select = $('select', container);
    const items = $('.dropdown-items', container);
    if (!is(select) || !is(items)) {
        return;
    }

    for (const option of select.children) {
        if (is(option.dataset.eternal)) {
            continue;
        }

        option.remove();
    }

    items.textContent = '';
}

/**
 * @param {string} label
 * @param {string} value
 * @return {HTMLDivElement}
 */
function form_select_Option(label, value) {
    return jsml.div({
        class: 'dropdown-item',
        'data-value': value,
    }, label);
}

/**
 * @param {HTMLElement} container
 * @param {string} label
 * @param {string} value
 */
function form_select_addOption(container, label, value) {
    const select = $('select', container);
    const items = $('.dropdown-items', container);
    if (!is(select) || !is(items)) {
        return;
    }

    select.append(new Option(label, value));
    items.append(form_select_Option(label, value));
}

function form_select_showOptions(container) {
    dropdown_expand(container, $('.dropdown', container));
}

function form_select_hideOptions(container) {
    dropdown_shrink(container, $('.dropdown', container));
}

/**
 * @param {HTMLElement} container
 * @param {string} label
 */
function form_select_setDropdownLabel(container, label) {
    const element = $('.option', container);

    if (is(element)) {
        element.textContent = label;
    }
}

/**
 * @param {HTMLElement} container
 * @param {HTMLOptionElement} option
 */
function form_asyncSelect_onOptionSelected(container, option) {
    option.parentElement.value = option.value;
    option.parentElement.dispatchEvent(new Event('change', { bubbles: true }));

    const previous = option.parentElement?.querySelector('[data-eternal=true]');
    if (is(previous)) {
        delete previous.dataset.eternal;
    }

    option.dataset.eternal = 'true';

    form_select_setDropdownLabel(container, option.textContent);

    $('.select-search', container)?.blur();
    $('.dropdown-item.cursor', container)?.classList.remove('cursor');
}

/**
 * @param {HTMLElement} container
 * @param {HTMLOptionElement} option
 */
function form_select_onOptionSelected(container, option) {
    option.parentElement.value = option.value;
    option.parentElement.dispatchEvent(new Event('change', { bubbles: true }));

    form_select_setDropdownLabel(container, option.textContent);

    $('.select-search', container)?.blur();
    $('.dropdown-item.cursor', container)?.classList.remove('cursor');

    const dropdownItem = $(`.dropdown-item[data-value="${value}"]`, container);
    dropdownItem?.classList.add('cursor');
}

/**
 * @param {HTMLElement} container
 * @param {string} value
 * @returns {Opt<HTMLOptionElement>}
 */
function form_select_findOption(container, value) {
    let option = $(`select option[value="${value}"]`, container);
    if (is(option)) {
        return option;
    }

    /** @type {HTMLElement} */
    const select = $("select", container);
    for (const o of select.children) {
        if (o instanceof HTMLOptionElement && o.value === value) {
            return o;
        }
    }

    return undefined;
}

/**
 * @param {HTMLElement} container
 * @param {string} value
 * @param {(container: HTMLElement, option: HTMLOptionElement) => void} onOptionSelected
 */
function form_select_selectOption(container, value, onOptionSelected = form_select_onOptionSelected) {
    let option = form_select_findOption(container, value);
    if (!is(option)) {
        return;
    }

    const fn = std_getFunction(container.dataset.onOptionSelected)
        ?? onOptionSelected;

    fn(container, option);
}

/**
 * @param {HTMLElement} container
 * @param {string} query
 */
function form_select_search(container, query) {
    const q = query.toLowerCase();
    let first = undefined;

    for (const option of $$(".dropdown-item", container)) {
        const valid = option.textContent.toLowerCase().includes(q);
        option.classList.toggle('hide', !valid);
        option.classList.remove('cursor');

        if (valid && !is(first)) {
            first = option;
        }
    }

    first?.classList.add('cursor');
}

/**
 * @param {HTMLElement} container
 * @param {string} query
 * @return {Promise<void>}
 */
async function form_asyncSelect_search(container, query) {
    const minLength = container.dataset.searchMinLength ?? 3;
    if (query.length < minLength) {
        form_select_clearOptions(container);
        return;
    }

    const href = container.dataset.searchUrl;
    if (!is(href)) {
        return;
    }

    const url = new URL(href);
    url.searchParams.set(container.dataset.searchQueryArgument ?? 'q', query);

    const response = await fetch(std_jsonEndpoint(url));
    if (await std_fetch_handleServerError(response)) {
        return;
    }

    if (!response.ok) {
        console.error("Cannot load options: " + response.statusText);
        return;
    }

    form_select_clearOptions(container);
    for (const { label, value } of await response.json()) {
        form_select_addOption(container, label, value);
    }

    form_select_showOptions(container);
}

/**
 * @param {HTMLElement} container
 * @param {HTMLInputElement} searchInput
 * @param {HTMLElement} search
 * @param {HTMLElement} selection
 * @param {HTMLElement} dropdown
 * @param {HTMLElement} dropdownItems
 * @param {Function} defaultSearchFunction
 */
function form_select_searchInputInit(
    container, searchInput, search, selection, dropdown, dropdownItems, defaultSearchFunction
) {
    searchInput?.addEventListener('focus', () => {
        search.classList.remove('opacity-0');
        selection.classList.add('opacity-0');
        dropdown_expand(container, dropdown);

        setTimeout(() => {
            const selected = $('.cursor', dropdownItems);
            if (!is(selected)) {
                return;
            }

            std_dom_scrollIntoView(selected, dropdown);
        }, DROPDOWN_ANIMATION_DURATION);
    });

    searchInput?.addEventListener('blur', () => {
        search.classList.add('opacity-0');
        selection.classList.remove('opacity-0');
        dropdown_shrink(container, dropdown);
        searchInput.value = '';

        for (const option of $$(".dropdown-item", dropdownItems)) {
            option.classList.remove('hide');
        }
    });

    const searchFunction = std_getFunction(container.dataset.search) ?? defaultSearchFunction;
    searchInput?.addEventListener('input', () => {
        searchFunction(container, searchInput.value);
    });
}

/**
 * @param {HTMLElement} dropdown
 * @param {HTMLElement} dropdownItems
 * @param {"up" | "down"} direction
 * @param {SkipPredicate<HTMLElement>} skipPredicate
 */
function form_select_moveCursor(dropdown, dropdownItems, direction, skipPredicate) {
    const cursor = $('.cursor', dropdownItems);
    if (!is(cursor)) {
        if (dropdownItems.children.length === 0) {
            return;
        }

        const target = skipPredicate(dropdownItems.children[0])
            ? std_dom_findChild(dropdownItems.children[0], std_dom_nextChild, skipPredicate)
            : dropdownItems.children[0];

        target?.classList.add('cursor');
        return;
    }

    cursor.classList.remove('cursor');
    let target;

    if (direction === "up") {
        target = std_dom_findChild(cursor, std_dom_previousChild, skipPredicate);
    } else if (direction === "down") {
        target = std_dom_findChild(cursor, std_dom_nextChild, skipPredicate);
    }

    if (!is(target)) {
        return;
    }

    target.classList.add('cursor');
    std_dom_scrollIntoView(target, dropdown);
}

/**
 * @param {HTMLElement} current
 * @return {boolean}
 */
function form_select_skipPredicate(current) {
    return current.classList.contains('hide');
}

/**
 * @param {HTMLElement} container
 */
function form_select_init(container) {
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
                form_select_moveCursor(dropdown, dropdownItems, "up", form_select_skipPredicate);
            }

            if (event.key === "ArrowDown") {
                form_select_moveCursor(dropdown, dropdownItems, "down", form_select_skipPredicate);
            }
        }

        if (event.key === "Enter") {
            event.preventDefault();

            const cursor = $('.cursor', dropdownItems);
            if (!is(cursor) || form_select_skipPredicate(cursor)) {
                return;
            }

            form_select_selectOption(container, cursor.dataset.value);
        }
    });

    dropdownItems.addEventListener('pointerdown', event => {
        const item = event instanceof Element && event.target.classList.contains('dropdown-item')
            ? event.target
            : event.target.closest('.dropdown-item');

        if (!is(item)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        form_select_selectOption(container, item.dataset.value);
    });

    dropdown_shrink(container, dropdown);
    dropdown.classList.remove('hide');
    dropdown_animate(dropdown, true);
}
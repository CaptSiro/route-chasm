const EVENT_TOOL_BAR_ISSUE_STOP_DROP = 'toolBarStopDrop';



/**
 * @param {HTMLElement} element
 */
function toolBar_init(element) {
    let dropped = false;
    /** @type {HTMLElement|null} */
    let current = null;

    const topLevel = $$('.menu-item-top', element);

    /**
     * @param {HTMLElement} element
     */
    const changeCurrent = element => {
        if (element == null) return;

        const locked = [];
        if (current !== null) {
            current.classList.remove("drop", "locked");
            locked.push(current.classList);
        }

        current = element;
        dropped = true;
        current.classList.add("drop", "lock");

        locked.push(current.classList);
        setTimeout(() => {
            locked.forEach(l => l.remove("lock"));
        }, 250);
    };

    /**
     * @param {HTMLElement} item
     * @returns {boolean}
     */
    const isPartOfDrop = item => {
        return (item.closest(".drop.menu-item-top") !== null);
    };

    /**
     * @param {HTMLElement} child
     * @returns {HTMLElement}
     */
    const findTopItem = child => {
        let top = null;
        let next = child;

        while (next != null) {
            if (next.classList.contains("menu-item-top")) {
                top = next;
            }
            next = next.parentElement;
        }

        return top;
    }

    const stopDrop = () => {
        for (const item of topLevel) {
            item.classList.remove("drop");
        }

        dropped = false;
    }

    element.addEventListener(EVENT_TOOL_BAR_ISSUE_STOP_DROP, stopDrop);

    window.addEventListener("click", event => {
        if (isPartOfDrop(event.target)) {
            return;
        }

        stopDrop();
    });

    for (const item of topLevel) {
        item.addEventListener("click", event => {
            changeCurrent(findTopItem(event.target));
        });

        item.addEventListener("mouseenter", event => {
            if (dropped === true && !item.classList.contains("drop")) {
                changeCurrent(findTopItem(event.target));
            }
        });
    }
}

/**
 * @param {HTMLElement} element
 */
function toolBar_item(element) {
    const action = std_getFunction(element.dataset.action);

    element.addEventListener("click", event => {
        if (is(action)) {
            try {
                action(element);
            } catch (e) {
                console.error(e);
            }
        }

        element.dispatchEvent(new CustomEvent(EVENT_TOOL_BAR_ISSUE_STOP_DROP, { bubbles: true }));
        event.stopImmediatePropagation();
    });

    const shortcut = element.dataset.shortcut;
    if (is(shortcut)) {
        Shortcut.register(event => {
            if (!is(action)) {
                return;
            }

            try {
                action(element, event);
                event.preventDefault();
                event.stopImmediatePropagation();
            } catch (e) {
                console.error(e);
            }
        }, shortcut);
    }
}

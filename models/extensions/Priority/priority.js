/**
 * @param {HTMLElement} grid
 */
function prio_addDragListeners(grid) {
    if (grid.dataset.priorityListners === "set") {
        return;
    }

    grid.dataset.priorityListners = "set";
    let dragged = null;

    grid.addEventListener('dragstart', event => {
        const row = event.target.closest('.grid-row');
        if (!is(row)) {
            return;
        }

        dragged = row;
        row.classList.add('dragging');
    });

    grid.addEventListener('dragend', () => {
        if (is(dragged)) {
            dragged.classList.remove("dragging");
        }

        $$('.over', grid)
            .forEach(x => x.classList.remove('over'));
    });

    grid.addEventListener('dragover', event => {
        event.preventDefault();

        const target = event.target.closest('.grid-row');
        if (!is(target) || target === dragged) {
            return;
        }

        $$('.over', grid)
            .forEach(x => x.classList.remove('over'));

        target.classList.add('over');
    });

    grid.addEventListener('drop', async event => {
        event.preventDefault();

        const target = event.target.closest('.grid-row');
        if (!is(target) || target === dragged) {
            return;
        }

        target.classList.remove('over');

        const handle = $('.prio-drag-handle', dragged);
        if (!is(handle)) {
            return;
        }

        const rows = [...$$('.grid-row', grid)];
        const draggedIndex = rows.indexOf(dragged);
        const targetIndex = rows.indexOf(target);

        if (draggedIndex < targetIndex) {
            target.after(dragged);
        } else {
            target.before(dragged);
        }

        const url = new URL(handle.dataset.url);
        url.searchParams.set(handle.dataset.priorityQuery, String(targetIndex));

        const response = await fetch(url);
        if (await std_fetch_handleServerError(response)) {
            return;
        }

        std_fetch_follow(response);
    });
}

/**
 * @param {HTMLElement} element
 * @param {string} url
 */
function prio_dragHandle(element, { url }) {
    element.closest('.grid-row')
        ?.setAttribute('draggable', 'true');

    const grid = element.closest('.grid');
    if (!is(grid)) {
        return;
    }

    prio_addDragListeners(grid);
}
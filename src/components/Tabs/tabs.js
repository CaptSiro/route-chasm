function tabs(element) {
    const labels = $$(".label", element);
    const wrappers = $$(".tabs__wrapper", element);
    const elements = Array.from(labels).concat(Array.from(wrappers));

    for (const label of labels) {
        label.addEventListener("click", () => {
            const l = label.dataset.label;

            for (const e of elements) {
                e.classList.toggle('selected', e?.dataset?.label === l);
            }
        });
    }
}
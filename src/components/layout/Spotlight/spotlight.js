/**
 * @param {HTMLElement} element
 */
function spotlight_switch(element) {
    element.addEventListener("click", () => {
        const label = element.dataset.spotlightSwitch;
        const item = $(`[data-spotlight-item="${label}"]`);
        if (!is(item)) {
            return;
        }

        const spotlight = item.closest('.spotlight');
        if (!is(spotlight)) {
            return;
        }

        const visible = $('.spotlight-visible', spotlight);

        visible?.classList.remove('spotlight-visible');
        item.classList.add('spotlight-visible');
    });
}
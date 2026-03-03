/**
 * @param {HTMLElement} element
 * @param {string} url
 */
function listing_card(element, { url }) {
    element.addEventListener('click', () => {
        window.open(url, "_self");
    });

    element.addEventListener('mouseup', event => {
        window.open(url, "_blank");
        event.preventDefault();
    });
}
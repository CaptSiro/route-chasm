/**
 * @param {HTMLElement} element
 * @param {string} url
 */
function listing_card(element, { url }) {
    element.addEventListener('click', () => {
        window.open(url, "_self");
    });
}
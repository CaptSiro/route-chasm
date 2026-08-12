/**
 * @param {Element} element
 */
function dashboard_sideBarItem(element) {
    element.addEventListener("click", () => {
        $('a', element)?.click();
        $('.target', element)?.click();
    });
}
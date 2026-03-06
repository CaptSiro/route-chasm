/**
 * @param {HTMLElement} element
 * @param {string} minLength
 */
function search_headerSearch(element, { minLength }) {
    const input = $('#header-search', element);
    const dropdown = $('.header-search-dropdown', element);

    input?.addEventListener('input', async event => {
        const value = input.value.trim();
        if (value.length < Number(minLength)) {
            return;
        }

        const api = api_loadSearch();
        if (!is(api)) {
            return;
        }

        const url = new URL(api.searchFullTextUrl);
        url.searchParams.set(api.searchQuery, value);
        url.searchParams.set('o', 'html');

        const response = await fetch(url);
        if (await std_fetch_handleServerError(response)) {
            return;
        }

        dropdown.innerHTML = await response.text();
    });
}
/**
 * @param {HTMLElement} element
 * @param {string} minLength
 * @param {?string} url
 */
function search_headerSearch(element, { minLength, url }) {
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

        const request = new URL(is(url)
            ? url
            : api.searchFullTextUrl);

        request.searchParams.set(api.searchQuery, value);
        request.searchParams.set('o', 'html');

        const response = await fetch(request);
        if (await std_fetch_handleServerError(response)) {
            return;
        }

        dropdown.innerHTML = await response.text();
    });
}
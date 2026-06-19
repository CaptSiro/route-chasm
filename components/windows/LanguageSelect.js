/**
 * @param {HTMLElement} element
 */
function languageSelect(element) {
    const api = api_loadLocalization();
    if (!is(api)) {
        return;
    }

    element.textContent = '';
    for (const localization of api.localizations) {
        if (localization.language === api.language) {
            element.append(jsml.button({
                class: 'button',
                disabled: true
            }, localization.language));
            continue;
        }

        element.append(
            jsml.button({
                class: 'button',
                onClick: () => location.replace(localization.url),
            }, localization.language)
        );
    }
}

/**
 * @param {HTMLElement} element
 */
function languageSelect_languageButton(element) {
    element.addEventListener('click', () => {
        const url = new URL(location.href);

        if (url.searchParams.get("l") === element.dataset.code) {
            window_issueClose(element);
            return;
        }

        url.searchParams.set("l", element.dataset.code);
        location.replace(url);
    });
}
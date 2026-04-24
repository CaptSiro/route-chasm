function api_getObject(selector) {
    const text = $(selector)?.textContent;
    if (!is(text)) {
        return null;
    }

    return JSON.parse(text);
}



/**
 * @return {FileSystemApi|null|any}
 */
function api_loadFileSystem() {
    const api = api_getObject("#api-file-system");

    const variantQuery = api['variantQuery'];
    const nameQuery = api['nameQuery'] ?? 'name';

    const createHashedUrl = (url, hash, query = {}) => {
        if (!is(url)) {
            return null;
        }

        const u = new URL(url);
        u.pathname += '/' + hash;

        for (const key in query) {
            if (!is(query[key])) {
                continue;
            }

            u.searchParams.set(key, query[key]);
        }

        return u.href;
    };

    return {
        directoryUrl: api.directoryUrl,
        imageVariantUrl: api.imageVariantUrl,

        createFileUrl(hash, variant = null) {
            return createHashedUrl(api['fileUrl'], hash, {
                [variantQuery]: variant
            });
        },

        createDownloadUrl(hash, name, variant = null) {
            return createHashedUrl(api['downloadUrl'], hash, {
                [variantQuery]: variant,
                [nameQuery]: name
            });
        },

        createInfoUrl(hash) {
            return createHashedUrl(api['infoUrl'], hash);
        },

        createDirectoryUrl(type) {
            if (!is(type)) {
                return api.directoryUrl;
            }

            const url = new URL(api.directoryUrl);
            url.searchParams.set(api['fileTypeQuery'], type);

            return url.href;
        }
    };
}



/**
 * @return {LocalizationApi|null|any}
 */
function api_loadLocalization() {
    return api_getObject("#api-localization");
}



/**
 * @return {SearchApi|null|any}
 */
function api_loadSearch() {
    return api_getObject("#api-search");
}



/**
 * @return {StartuhApi|null|any}
 */
function api_loadStartuh() {
    return api_getObject("#api-startuh");
}
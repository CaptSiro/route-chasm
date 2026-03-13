async function docs_documentPage(element) {
    const w = window_createNotice('Fetching documentation', { isDialog: true });
    window_open(w);

    const url = new URL(location.href);
    url.searchParams.set('content', '');

    const response = await fetch(url);
    window_close(w);

    if (await std_fetch_handleServerError(response)) {
        return;
    }

    const content = $('.article-content', element);
    if (!is(content)) {
        return;
    }

    /** @type { {content: string, related: [] } } */
    const document = await response.json();
    const hasRelated = is(document.related) && document.related.length > 0;

    const markdown = new Markdown(document.content);
    content.append(markdown.getHtml());

    const tableOfContents = $(".article-toc-content", element);
    if (is(tableOfContents)) {
        tableOfContents.append(markdown.getTableOfContents());
    }

    const related = $('.article-related-content', element);
    related?.classList.toggle('display-none', !hasRelated);
    if (is(related) && hasRelated) {
        for (const fragment of document.related) {
            related.append(jsml.a({ href: fragment.link }, fragment.label));
        }
    }

    std_dom_scrollToFragment();
}
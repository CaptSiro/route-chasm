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

    const tableOfContents = $(".article-toc-content", element);

    /** @type { {content: string, related: [] } } */
    const document = await response.json();
    const hasRelated = is(document.related) && document.related.length > 0;

    /** @param {Markdown} markdown */
    const displayMarkdown = markdown => {
        content.textContent = '';
        content.append(markdown.getHtml());

        if (!is(tableOfContents)) {
            return;
        }

        tableOfContents.textContent = '';
        tableOfContents.append(markdown.getTableOfContents());
    }

    displayMarkdown(new Markdown(document.content));

    const editor = $('.article-editor', element);
    if (is(editor)) {
        const aside = $('.article-toc-wrapper', element);

        editor.textContent = document.content;
        editor.classList.add('display-none');

        $('.md-editor-tool-bar .md-edit', element)?.addEventListener('click', () => {
            if (!editor.classList.contains('display-none')) {
                displayMarkdown(new Markdown(std_dom_getWhitespaceTextContent(editor)));
            }

            element.classList.toggle('no-columns');
            aside?.classList.toggle('display-none');
            editor.classList.toggle('display-none');
            content.classList.toggle('display-none');
        });

        $('.md-editor-tool-bar .md-save', element)?.addEventListener('click', async () => {
            const w = window_createNotice('Saving documentation...', { isDialog: true });
            window_open(w);

            const response = await fetch(std_jsonEndpoint(location.href), {
                method: "POST",
                body: JSON.stringify({ content: std_dom_getWhitespaceTextContent(editor) })
            });

            window_close(w);
            if (await std_fetch_handleServerError(response)) {
                return;
            }

            if (!response.ok) {
                await window_alert('Saving documentation failed: ' + response.statusText);
                return;
            }

            await window_alert('Documentation saved successfully');
        });

        $('.md-editor-tool-bar .md-re-generate', element)?.addEventListener('click', async () => {
            if (!(await window_confirm(
                'Do you want to re-generate the documentation? (This action may take a few minutes)',
                { isDialog: true }
            ))) {
                return;
            }

            const w = window_createNotice('Re-generating documentation...', { isDialog: true });
            window_open(w);

            const response = await fetch(std_jsonEndpoint(location.href), { method: "PUT" });

            window_close(w);
            if (await std_fetch_handleServerError(response)) {
                return;
            }

            if (!response.ok) {
                await window_alert('Re-generating documentation failed: ' + response.statusText);
                return;
            }

            location.reload();
        });
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
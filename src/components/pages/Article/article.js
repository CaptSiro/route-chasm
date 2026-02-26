/**
 * @param {HTMLElement} element
 */
function article(element) {
    const data = $("#data-content", element);
    const content = $('.article-content', element);

    if (!is(data) || !is(content)) {
        return;
    }

    const markdown = new Markdown(std_dom_getWhitespaceTextContent(data));
    content.append(markdown.getHtml());

    const tableOfContents = $(".article-toc", element);
    if (is(tableOfContents)) {
        tableOfContents.append(markdown.getTableOfContents());
    }

    const gallery = $('.article-gallery', element);
    if (is(gallery)) {
        gallery.append(markdown.getGallery());
    }
}

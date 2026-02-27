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
    const gallery = new Gallery();

    content.append(markdown.getHtml((element, node) => {
        if (node.type !== "IMAGE") {
            return;
        }

        element.classList.add('article-gallery-image');
        const cursor = gallery.add(node.src, node.alt, node.title);

        element.addEventListener("click", () => {
            gallery.show();
            gallery.goTo(cursor);
        });
    }));

    const tableOfContents = $(".article-toc-content", element);
    if (is(tableOfContents)) {
        tableOfContents.append(markdown.getTableOfContents());
    }

    const fragment = location.hash.startsWith('#')
        ? location.hash.substring(1)
        : location.hash;

    if (fragment.trim() === "") {
        return;
    }

    $('#' + fragment)?.scrollIntoView();
}

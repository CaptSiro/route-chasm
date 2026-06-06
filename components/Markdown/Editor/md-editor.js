/**
 * @param {HTMLElement} element
 */
function md_editor(element) {
    const editor = $('.editor', element);
    const display = $('.display', element);
    const area = $(".data-markdown", element);
    const content = $('.content', element);

    if (!is(editor) || !is(display) || !is(area) || !is(content)) {
        return;
    }

    $('.switch', element)?.addEventListener('click', () => {
        editor.classList.toggle('display-none');
        display.classList.toggle('display-none');

        const markdown = new Markdown(std_dom_getWhitespaceTextContent(area));

        content.textContent = '';
        content.append(markdown.getHtml());
    });
}
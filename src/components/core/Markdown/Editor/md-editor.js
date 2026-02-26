/**
 * @param {HTMLElement} element
 */
function md_editor(element) {
    const display = $('.display', element);
    const editor = $(".data-markdown", element);
    const content = $('.content', element);

    if (!is(display) || !is(editor) || !is(content)) {
        return;
    }

    $('.switch', element)?.addEventListener('click', () => {
        editor.classList.toggle('display-none');
        display.classList.toggle('display-none');

        const sourceCode = editor instanceof HTMLTextAreaElement
            ? editor.value
            : std_dom_getWhitespaceTextContent(editor);

        const markdown = new Markdown(sourceCode);

        console.log(sourceCode);

        content.textContent = '';
        content.append(markdown.getHtml());
    });
}
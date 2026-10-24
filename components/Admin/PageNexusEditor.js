function pageEditor_isStructureDescription(description) {
    const type = {
        'title': 'string',
        'template': 'string',
        'children': 'object',
    };

    for (const key in type) {
        if (typeof description[key] !== type[key]) {
            return false;
        }
    }

    return true;
}

/**
 * @typedef {{ title: string, template: string, children: PageEditorDescription[] }} PageEditorDescription
 */

/**
 *
 * @param {PageEditorDescription} props
 * @return {HTMLDivElement|JsmlContent}
 */
function PageEditorStructure(props) {
    if (!pageEditor_isStructureDescription(props)) {
        return;
    }
    
    return html`
        <div>
            <span>
                <span class="title">${props.title}</span> <span class="template">${props.template}</span>
            </span>
            
            <ul>
                <${For} each="${props.children}">
                    ${x => PageEditorStructure(x)}
                </>
            </ul>
        </div>
    `;
}

/**
 * @param {HTMLFormElement} form
 * @param {Response} response
 * @return {Promise<void>}
 */
async function pageEditor_onStructureSubmitSuccess(form, response) {
    if (response.headers.get('Content-Type') !== 'application/json') {
        return;
    }

    const structure = await response.json();
    if (!Array.isArray(structure)) {
        return;
    }

    window_open(window_create('Generated structure', html`
        <div class="structure">
            <ul>
                <${For} each="${structure}">
                    ${p => html`
                        <li>
                            <${PageEditorStructure} ...${p} />
                        </li>
                    `}
                </>
            </ul>
        </div>
        
        <div class="row">
            <button onClick="${() => location.reload()}">
                Reload to see effects
            </button>
        </div>
    `));
}
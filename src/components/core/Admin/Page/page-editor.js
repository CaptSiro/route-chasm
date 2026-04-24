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

function pageEditor_structure(description) {
    if (!pageEditor_isStructureDescription(description)) {
        return;
    }

    return jsml.div(_, [
        jsml.span(_, [
            jsml.span('title', description.title),
            " ",
            jsml.span('template', `(${description.template})`)
        ]),
        jsml.ul(_, description.children.map(x =>
            jsml.li(_, pageEditor_structure(x))
        ))
    ]);
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

    const w = window_create('Generated structure', [
        jsml.div('structure',
            jsml.ul(_, structure.map(x =>
                jsml.li(_, pageEditor_structure(x))
            ))
        ),
        jsml.div('row', [
            jsml.button({
                onClick: () => location.reload(),
            }, 'Reload to see effects')
        ])
    ]);

    window_open(w);
}
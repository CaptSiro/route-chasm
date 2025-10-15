let EDITOR_VIEWPORT_RESIZE_ANIMATION_DURATION = 250;


/** @type {EditorViewportMode} */
const EDITOR_VIEWPORT_MODE_COMPUTER = {
    name: 'computer',
    aspectRatio: 16 / 9
};

/** @type {EditorViewportMode} */
const EDITOR_VIEWPORT_MODE_MOBILE = {
    name: 'mobile',
    aspectRatio: 9 / 16
};



/** @type {EditorViewportResizeListener[]} */
const editor_viewport_listeners = [];
const editor_inspector = $(".table > .inspector");
const editor_nav = $("nav");
const editor_viewport_mount = $(".viewport-mount");
const editor_viewport = $("#viewport", editor_viewport_mount);
/** @type {EditorViewportDimension} */
let editor_viewport_dimension;
/** @type {EditorViewportMode} */
let editor_viewport_mode = EDITOR_VIEWPORT_MODE_COMPUTER;
window.addEventListener("resize", editor_viewport_resize);
window.addEventListener("load", editor_viewport_resize);

editor_viewport_onResize(dimensions => {
    editor_viewport.animate({
        width: dimensions.width + "px",
        height: dimensions.height + "px"
    }, {
        duration: EDITOR_VIEWPORT_RESIZE_ANIMATION_DURATION,
        fill: "forwards"
    });
});

editor_viewport_resize();

/**
 * @param {EditorViewportResizeListener} callback
 */
function editor_viewport_onResize(callback) {
    editor_viewport_listeners.push(callback);
}

function editor_viewport_resize() {
    const box = editor_viewport_mount.getBoundingClientRect();
    const maxWidth = box.width;
    const maxHeight = box.height;

    editor_viewport.className = '';
    editor_viewport.classList.add(editor_viewport_mode.name);

    const targetWidth = maxHeight * editor_viewport_mode.aspectRatio;
    const targetHeight = maxWidth / editor_viewport_mode.aspectRatio;

    editor_viewport_dimension = {
        targetAspectRatio: editor_viewport_mode.aspectRatio,
        maxWidth,
        maxHeight,
        targetWidth,
        targetHeight,
        width: std_clamp(300, maxWidth, targetWidth),
        height: Math.min(targetHeight, maxHeight),
    };

    for (const viewportListener of editor_viewport_listeners) {
        viewportListener(editor_viewport_dimension);
    }
}

/**
 * @param {EditorViewportMode|HTMLElement} arg
 */
function editor_viewport_setMode(arg) {
    /** @type {EditorViewportMode} */
    let mode;
    
    if (arg instanceof HTMLElement) {
        const element = arg;
        const aspectRatio = std_evaluate(element.dataset.aspectRatio);
        const name = element.dataset.name;
        
        mode = {
            name,
            aspectRatio
        }
    } else {
        mode = arg;
    }

    editor_viewport_mode = mode;
    editor_viewport_resize();
}



// $$(".id > img").forEach(img => {
//   img.addEventListener("pointerdown", evt => {
//     img.closest(".w-category").classList.toggle("expanded");
//   });
// });



//? controls resizer
// $$(".controls .resize-divider").forEach(rd => {
//   rd.addEventListener("pointerdown", evt => {
//     rd.setPointerCapture(evt.pointerId);
//
//     const pmove = pmoveEvt => {
//       const parentHeight = rd.parentElement.clientHeight;
//       const rectPrev = rd.previousElementSibling.getBoundingClientRect();
//       const cursorTop = pmoveEvt.clientY;
//
//       const prev = parentHeight + (cursorTop - (rectPrev.top + parentHeight));
//       const next = parentHeight - prev;
//
//       if (prev > 50 && next > 50) {
//         rd.previousElementSibling.style.height = prev + "px";
//         rd.nextElementSibling.style.height = next + "px";
//       }
//     };
//
//     rd.addEventListener("pointermove", pmove);
//
//     rd.addEventListener("pointerup", _ => {
//       rd.removeEventListener("pointermove", pmove);
//     });
//   });
// });



//? widget menu
const widgetSelect = $("#widget-select-mount");
/** @type {HTMLElement} */
let selectedWidget = undefined;
let isInSearchMode = false;
let currentCmd;

/**
 * @param {boolean} isInSearch
 */
function setSearchMode(isInSearch) {
    isInSearchMode = isInSearch;
    widgetSelect.classList.toggle("search-mode", isInSearch);

    if (isInSearch === false) {
        $$(".not-search-satisfactory, .search-satisfactory").forEach(e => {
            e.classList.remove("not-search-satisfactory");
            e.classList.remove("search-satisfactory");
        });
    }
}

/**
 * @param {boolean} direction true => Up; false => Down
 */
function moveSelection(direction) {
    if (selectedWidget === undefined) {
        const widgetPool = isInSearchMode
            ? widgetSelect.querySelectorAll(".widget-option.search-satisfactory")
            : widgetSelect.querySelectorAll(".widget-option");

        selectedWidget = widgetPool[!direction ? 0 : (widgetPool.length - 1)];
        selectedWidget.classList.add("selected");
        return;
    }

    selectedWidget.classList.remove("selected");

    /** @type {HTMLElement[]} */
    let selectionPool = Array.from(widgetSelect.querySelectorAll(".widget-option"));
    if (direction) {
        selectionPool = selectionPool.reverse();
    }

    let pointer = selectionPool.indexOf(selectedWidget);

    do {
        pointer++;
        if (pointer === selectionPool.length) {
            pointer = 0;
        }

        if (isInSearchMode === false || selectionPool[pointer].classList.contains("search-satisfactory")) {
            selectedWidget = selectionPool[pointer];
            break;
        }
    } while (selectionPool[pointer] !== selectedWidget);

    selectedWidget.classList.add("selected");

    std_dom_scrollIntoView(selectedWidget, widgetSelect);
}


const defs = $("#icon-definitions");
// AJAX.get("/bundler/resource/*", JSONHandlerSync(resources => {
//     /** @type {Map<string, { properties: { category: string, label: string, class: string, searchIndex: string }, files: { icon: string } }[]>} */
//     const grouped = Array.from(resources)
//         .filter(resource => resource.properties.category !== "Hidden")
//         .reduce((map, resource) => {
//             resource.properties.searchIndex = resource.properties.category + "_" + resource.properties.label;
//
//             if (map.has(resource.properties.category)) {
//                 map.get(resource.properties.category).push(resource);
//             } else {
//                 map.set(resource.properties.category, [resource]);
//             }
//
//             return map;
//         }, new Map());
//
//     widgetSelect.textContent = "";
//     // const filenameRegex = /^.*[\\\/]/;
//     for (const key of Array.from(grouped.keys()).sort()) {
//         widgetSelect.appendChild(
//             Div("widget-category", [
//                 Div("label",
//                     Heading(3, __, key)
//                 ),
//                 Div("content",
//                     grouped.get(key).map(resource => {
//                         defs.appendChild(
//                             stringToSVGDef(resource.files.icon, resource.properties.class)
//                         );
//
//                         return (
//                             Div("widget-option", [
//                                 SVG(resource.properties.class),
//                                 Span(__, resource.properties.label)
//                             ], {
//                                 listeners: {
//                                     click: function () {
//                                         if (currentCmd === undefined) return;
//                                         currentCmd.replaceSelf(widgets.get(resource.properties.class).default(currentCmd.parentWidget, true));
//                                         widgetSelect.style.visibility = "hidden";
//                                     },
//                                     mouseover: function () {
//                                         widgetSelect.querySelectorAll(".widget-option").forEach(w => w.classList.remove("selected"));
//                                         selectedWidget = this;
//                                         this.classList.add("selected");
//                                     }
//                                 },
//                                 modify: widgetElement => {
//                                     widgetElement.dataset.search = resource.properties.searchIndex;
//                                     widgetElement.dataset.class = resource.properties.class;
//                                 }
//                             })
//                         );
//                     })
//                 )
//             ])
//         );
//     }
// }));
window.addEventListener("load", async () => {
    const dataElement = $("#page-data");
    todo('Implement editor-data loading')
    const root = await WRoot.build(JSON.parse(dataElement.textContent), null, true);

    dataElement.remove();
    window.rootWidget = root;
    document.widgetElement = root;
    document.querySelector("#viewport").appendChild(root.rootElement);
    root.rootElement.click();
});



/**
 * @type {HTMLElement | undefined}
 */
let widgetSelectAnchor = undefined;

function unfollowWidgetSelect() {
    widgetSelectAnchor = undefined;
}

editor_viewport_onResize(() => {
    if (widgetSelectAnchor === undefined || widgetSelectAnchor.parentElement === null || widgetSelectAnchor.parentElement === undefined) return;

    setTimeout(() => {
        const rect = widgetSelectAnchor.getBoundingClientRect();
        widgetSelect.style.left = (rect.x - widgetSelectAnchor.parentElement.getBoundingClientRect().x) + "px";
        widgetSelect.style.top = (widgetSelectAnchor.offsetTop + rect.height) + "px";
    }, 0);
});

/**
 * @param {HTMLElement} to
 */
function moveWidgetSelect(to) {
    if ("widget" in to) {
        currentCmd = to.widget;
    }

    to.scrollIntoView();
    widgetSelectAnchor = to;

    const toBoundingBox = to.getBoundingClientRect();
    widgetSelect.style.left = (toBoundingBox.x - to.parentElement.getBoundingClientRect().x) + "px";
    widgetSelect.style.top = (to.offsetTop + toBoundingBox.height) + "px";
    // const mountBoundingBox = viewportMount.getBoundingClientRect();
    // const selectBoundingBox = widgetSelect.getBoundingClientRect();
    //
    // let left = toBoundingBox.left;
    // if (left + selectBoundingBox.width > mountBoundingBox.width) {
    //   left = mountBoundingBox.width - selectBoundingBox.width;
    // }
    // left /= (mountBoundingBox.width / 100);
    //
    // let top = toBoundingBox.top + toBoundingBox.height;
    // if (top + selectBoundingBox.height > mountBoundingBox.height) {
    //   top = toBoundingBox.top - selectBoundingBox.height;
    // }
    // top -= mountBoundingBox.top;
    // top /= (mountBoundingBox.height / 100);
    //
    // widgetSelect.style.left = left + "%";
    // widgetSelect.style.top = top + "%";

    widgetSelect.style.visibility = "visible";
}



// const fileSelectModal = $("#file-select");
// const filesModal = fileSelectModal.querySelector(".files");
// const filesError = fileSelectModal.querySelector(".error-modal");
// const filesModalInfiniteScroller = new InfiniteScroller(filesModal, async (index) => {
//     const files = await AJAX.get(`/file/${index}/?type=${fileSelectModal.dataset.fileType}`, JSONHandler());
//
//     let element;
//     for (const file of files) {
//         const fileURL = `${AJAX.SERVER_HOME}/file/${webpage.src}/${file.src}${file.extension}`;
//         element = Div("item", [
//             FileIcon(file.mimeContentType, { "image": fileURL + "?width=150" }),
//             Paragraph(__, String(file.basename + file.extension))
//         ], {
//             listeners: {
//                 click: evt => {
//                     if (fileSelectModal.dataset.multiple !== "true") {
//                         for (let child of evt.currentTarget.parentElement.children) {
//                             child.classList.remove("selected");
//                             child.dataset.selected = "false";
//                         }
//                     }
//                     evt.currentTarget.dataset.selected = String(!(evt.currentTarget.dataset.selected ?? false));
//                     evt.currentTarget.classList.toggle("selected", !!evt.currentTarget.dataset.selected);
//                 }
//             },
//             attributes: {
//                 title: String(file.basename + file.extension),
//                 "data-url": fileURL,
//                 "data-name": file.basename + file.extension,
//                 "data-server": file.src + file.extension,
//                 "data-src": file.src
//             }
//         });
//         filesModal.appendChild(element);
//     }
//
//     return element;
// }, undefined, false);
// fileSelectModal.addEventListener("fetch", () => {
//     filesModalInfiniteScroller.reset();
// });
// fileSelectModal.querySelector("button[type=submit]").addEventListener("click", () => {
//     const selected = Array.from(filesModal.children)
//         .filter(file => file.classList.contains("selected"))
//         .map(file => ({
//             url: file.dataset.url,
//             name: file.dataset.name,
//             src: file.dataset.src,
//             serverName: file.dataset.server
//         }));
//
//     if (selected.length === 0) {
//         return;
//     }
//
//     fileSelectModal.dispatchEvent(new CustomEvent("submit", { detail: selected }));
//     clearWindows();
// });
// fileSelectModal.querySelector("#file-upload-input").addEventListener("change", async evt => {
//     const body = new FormData();
//     for (const file of evt.target.files) {
//         body.append("uploaded[]", file);
//     }
//
//     const files = await AJAX.post("/file/collect", JSONHandler(), { body });
//
//     if (files.error) {
//         filesError.textContent = files.error;
//         filesError.classList.add("show");
//         return;
//     }
//
//     filesError.classList.remove("show");
//     filesModalInfiniteScroller.reset();
// });



// const themeCreatorModal = $("#theme-creator");
// const parameterA = themeCreatorModal.querySelector("#parameter-a");
// const parameterB = themeCreatorModal.querySelector("#parameter-b");
// // const colorBucketing = themeCreatorModal.querySelector("#color-group-size");
// const themeNameField = themeCreatorModal.querySelector("#theme-name");
// const themeCreatorError = themeCreatorModal.querySelector(".error");
// const themeCreatorSubmit = themeCreatorModal.querySelector("button[type=submit]");
// themeCreatorSubmit?.addEventListener("click", async () => {
//     if (themeCreatorModal.dataset.imageSource === "") return;
//
//     themeCreatorError.classList.remove("show");
//     themeCreatorError.textContent = "";
//
//     const name = themeNameField.value.trim();
//
//     if (name === "") {
//         themeCreatorError.classList.add("show");
//         themeCreatorError.textContent = "Name field is mandatory.";
//         return;
//     }
//
//     themeCreatorSubmit.disabled = true;
//     const generationResponse = await AJAX.post("/theme/generate", JSONHandler(), {
//         body: JSON.stringify({
//             imageSRC: themeCreatorModal.dataset.imageSource,
//             name: themeNameField.value,
//             a: +parameterA.value,
//             b: +parameterB.value
//         })
//     });
//
//     if (generationResponse.error !== undefined) {
//         console.log(generationResponse);
//         themeCreatorError.classList.add("show");
//         themeCreatorError.textContent = generationResponse.error;
//         themeCreatorSubmit.disabled = false;
//         return;
//     }
//
//     const themeChangeResponse = await AJAX.patch("/page/", JSONHandler(), {
//         body: JSON.stringify({
//             id: webpage.ID,
//             property: "themesSRC",
//             value: generationResponse.src
//         })
//     });
//
//     if (themeChangeResponse.error !== undefined) {
//         console.log(themeChangeResponse);
//         themeCreatorError.classList.add("show");
//         themeCreatorError.textContent = generationResponse.error;
//         themeCreatorSubmit.disabled = false;
//         return;
//     }
//
//     themeCreatorSubmit.disabled = false;
//     Theme.reset();
//     inspect(window.rootWidget.inspectorHTML, window.rootWidget);
// });
//
//
// const themeRenameModal = $("#theme-rename");
// const renameField = $("#theme-rename-field");
// themeRenameModal?.querySelector("button[type=submit]").addEventListener("click", async () => {
//     if (renameField.value.trim() === "" || themeRenameModal.dataset.src === undefined || themeRenameModal.dataset.src === "") {
//         return;
//     }
//
//     const response = await AJAX.patch(`/theme/rename/${themeRenameModal.dataset.src}`, JSONHandler(), {
//         body: JSON.stringify({ name: renameField.value })
//     });
//
//     if (response.error !== undefined) {
//         console.log(response);
//         return;
//     }
//
//     clearWindows();
//
//     Theme.resetUsersThemes();
//     inspect(window.rootWidget.inspectorHTML, window.rootWidget);
// });



let beingDragged;
let beingHovered;

//* Drag&Drop
document.body.addEventListener("drop", async evt => {
    evt.preventDefault();
    const dragHint = $(".drag-hint");
    const parentWidget = dragHint?.closest(".widget")?.widget;
    const dropAtContainerName = getClosestByClass(dragHint, "confined-container")?.constructor.name;

    if (dragHint !== null || !(parentWidget === null || parentWidget === undefined)) {
        const toBeMoved = $$("." + WIDGET_SELECTION_CLASS);
        for (const toBeMovedElement of toBeMoved) {
            if (!toBeMovedElement.classList.contains("widget")) continue;
            if (dropAtContainerName !== getClosestByClass(toBeMovedElement, "confined-container", false)?.constructor.name) continue;

            toBeMovedElement.widget.remove(false, false);
            await parentWidget.insertBeforeWidget(toBeMovedElement.widget, dragHint.nextElementSibling?.widget, false);
            dragHint.parentElement.insertBefore(toBeMovedElement, dragHint);
        }
    }

    cleanUpAfterDrag(evt);
});
document.body.addEventListener("dragend", evt => {
    cleanUpAfterDrag(evt);
});
document.body.addEventListener("dragover", evt => {
    if (getClosestByClass(beingDragged, "confined-container", false)?.constructor.name === getClosestByClass(evt.target, "confined-container", false)?.constructor.name) {
        evt.dataTransfer.dropEffect = "move";
        return;
    }

    evt.dataTransfer.dropEffect = "none";
    evt.stopPropagation();
});

async function cleanUpAfterDrag() {
    document.body.classList.remove("dragging");
    for (const widgetElement of $$("." + WIDGET_SELECTION_CLASS)) {
        widgetElement.classList.remove(WIDGET_SELECTION_CLASS);
    }

    await sleep(10);
    const dragHint = $(".drag-hint");

    if (dragHint === null) return;

    dragHint.classList.remove("expand");
    await sleep(100);
    dragHint.remove();
}

window.addEventListener("keydown", evt => {
    if (evt.key === "Escape") {
        edit_deselectAll();
    }

    if ((evt.key === "Delete" || evt.key === "Backspace") && evt.handledAction !== true) {
        edit_delete();
    }
});
window.addEventListener("mousemove", evt => {
    const hoveringOver = getClosestByClass(evt.target, "edit");

    if (hoveringOver === null) {
        beingHovered?.classList.remove("hover");
        beingHovered = undefined;
        return;
    }

    if (hoveringOver === beingHovered) {
        return;
    }

    beingHovered?.classList.remove("hover");
    hoveringOver.classList.add("hover");
    beingHovered = hoveringOver;
});



//* Copy&Paste
/**
 * @type {{element: HTMLElement, containerName: string}[]}
 */
let clipboardBuffer = [];

function edit_selectAll() {
    if (window.rootWidget === undefined) return;

    edit_deselectAll();

    for (const child of window.rootWidget.page.children) {
        child.select();
    }
}

function edit_deselectAll() {
    for (const selectedElement of $$("." + WIDGET_SELECTION_CLASS)) {
        selectedElement.classList.remove(WIDGET_SELECTION_CLASS);
    }
}

$("#edit-select-all")?.addEventListener("click", () => {
    edit_selectAll();
    stopDropdown();
});
$("#edit-deselect-all")?.addEventListener("click", () => {
    edit_deselectAll();
    stopDropdown();
});

function edit_delete() {
    for (const widgetElement of $$("." + WIDGET_SELECTION_CLASS)) {
        widgetElement?.widget.remove(true, true);
    }
}

function edit_copy(evt) {
    if (evt.clipboardActionHandled === true) return;

    evt.stopPropagation();
    evt.preventDefault();

    clipboardBuffer = Array.from($$("." + WIDGET_SELECTION_CLASS))
        .map(element => ({
            element,
            containerName: element?.widget.getClosestConfinedContainer()?.constructor.name
        }));
}

function edit_cut(evt) {
    if (evt.clipboardActionHandled === true) return;

    evt.stopPropagation();
    evt.preventDefault();

    clipboardBuffer = [];
    for (const element of $$("." + WIDGET_SELECTION_CLASS)) {
        if (element.widget === undefined) continue;

        const containerName = element.widget.getClosestConfinedContainer()?.constructor.name;
        element.classList.remove(WIDGET_SELECTION_CLASS);
        element?.widget.remove(true, true);

        clipboardBuffer.push({ element, containerName });
    }
}

function edit_paste(evt) {
    if (evt.clipboardActionHandled === true) return;

    evt.preventDefault();

    const selectedWidgets = Array.from($$("." + WIDGET_SELECTION_CLASS));
    if (selectedWidgets.length === 0) return;

    const insertAfter = selectedWidgets[selectedWidgets.length - 1];
    if (insertAfter === null) return;

    const parentWidget = insertAfter?.widget.parentWidget;
    if (parentWidget === undefined || parentWidget === null) return;

    const destinationContainerName = insertAfter.widget?.getClosestConfinedContainer()?.constructor.name;
    const insertBeforeChild = parentWidget.children[parentWidget.children.indexOf(insertAfter?.widget) + 1];

    let lastInsertedElement;
    for (const { element, containerName } of clipboardBuffer) {
        if (destinationContainerName !== containerName) continue;

        const widgetCopy = element?.widget.constructor.build(element?.widget.save(), parentWidget, rootWidget.editable);
        parentWidget.insertBeforeWidget(widgetCopy, insertBeforeChild);

        lastInsertedElement = widgetCopy.rootElement;
    }

    for (const element of selectedWidgets) {
        element.classList.remove(WIDGET_SELECTION_CLASS);
    }

    lastInsertedElement?.classList.add(WIDGET_SELECTION_CLASS);
}

$("#edit-delete")?.addEventListener("pointerdown", evt => {
    edit_delete(evt);
    stopDropdown();
    // evt.stopImmediatePropagation();
});
$("#edit-copy")?.addEventListener("pointerdown", evt => {
    edit_copy(evt);
    stopDropdown();
    // evt.stopImmediatePropagation();
});
$("#edit-cut")?.addEventListener("pointerdown", evt => {
    edit_cut(evt);
    stopDropdown();
    // evt.stopImmediatePropagation();
});
$("#edit-paste")?.addEventListener("pointerdown", evt => {
    edit_paste(evt);
    stopDropdown();
    // evt.stopImmediatePropagation();
});

window.addEventListener("cut", edit_cut);
window.addEventListener("copy", edit_copy);
window.addEventListener("paste", edit_paste);


/**
 * @param {HTMLElement} element
 * @param {string} className
 * @param {boolean} includeArgumentElement
 */
function getClosestByClass(element, className, includeArgumentElement = true) {
    if (element === undefined || element === null || element.classList === undefined) {
        return null;
    }

    do {
        if (element.classList.contains(className) && element.widget !== undefined && includeArgumentElement === true) {
            return element;
        }

        element = element.parentElement;
        includeArgumentElement = true;
    } while (element !== document.documentElement && element !== null);

    return null;
}



//* inspector
editor_inspector.textContent = "";

// const methods = () => false

// editor_inspector.append(
// CheckboxInspector(false, methods),
// CheckboxInspector(true, methods, "Hello"),
// TitleInspector("Hey i m a title"),
// RadioGroupInspector(methods, [{
//   text: "Male",
//   value: "male"
// }, {
//   text: "Female",
//   value: "female"
// }, {
//   text: "Other",
//   value: "other",
// }], "Gender"),
// HRInspector(),
// TextFieldInspector(__, methods, "Label:", "MY next project..."),
// TextAreaInspector(__, methods),
// TextAreaInspector("Hello there!", methods, "My area"),
// TextAreaInspector("Obi van Keno bi", methods, "My area", "Message"),
// NumberInspector(50, methods, "Age", "18", "lmaosobad"),
// NumberInspector(__, methods, "Width:", "20",
//   SelectInspector(methods, [{
//     text: "px",
//     value: "px"
//   }, {
//     text: "in",
//     value: "in"
//   }, {
//     text: "%",
//     value: "%",
//     selected: true
//   }], __, "small")
// ),
// DateInspector(new Date("2020-01-01"), methods, "Date of upload"),
// SelectInspector(methods, [{
//   text: "Male",
//   value: "male"
// }, {
//   text: "Female",
//   value: "female",
//   selected: true
// }, {
//   text: "Other",
//   value: "other"
// }], "My select", "x-large"),
// NotInspectorAble(),
// TextAreaInspector(__, methods),
// TextAreaInspector(__, methods),
// TextAreaInspector(__, methods),
// TextAreaInspector(__, methods),
// TextAreaInspector(__, methods),
// );

let currentlyInspecting;

/**
 * @param {ComponentContent} inspectorHTML
 * @param {Widget} widget
 */
function inspect(inspectorHTML, widget) {
    currentlyInspecting = widget;
    editor_inspector.textContent = "";
    editor_inspector.append(...parseComponentContent(inspectorHTML));
}


/**
 * @type {Object.<string, ()=>(void | Promise<void>)>}
 */
const shortCuts = {
    "s": file_save,
    "o": file_open,
    "e": file_exit
};
window.addEventListener("keydown", async evt => {
    for (const shortCutKey in shortCuts) {
        if (!(evt.key.toLowerCase() === shortCutKey && evt.ctrlKey)) continue;

        evt.preventDefault();
        evt.stopImmediatePropagation();
        await shortCuts[shortCutKey]();
        break;
    }
});

async function file_save() {
    const structure = window.rootWidget.save();

    const response = await AJAX.post("/page/" + webpage.src, JSONHandler(), {
        body: JSON.stringify({
            content: JSON.stringify(structure)
        })
    }).catch(errorResponse => {
        errorResponse.text().then(console.log);
    });

    if (response.error) {
        alert(response.error);
        return;
    }

    alert(response.message);
}

/**
 * @param {HTMLElement} element
 */
function file_open(element) {
    const url = element.dataset.url;
    if (!is(url)) {
        return;
    }

    open(url, "_blank");
}

function file_share() {
    navigator.clipboard.writeText(postLink)
        .then(() => alert("Link copied."));
}

function file_exit() {
    close();
}


/**
 * @template S
 * @callback Setter
 * @param {S} value
 * @param {HTMLElement} parentElement
 * @returns {boolean | Promise<boolean>}
 */

/**
 * @param {Event} evt
 * @param {Setter<string>} setter
 * @param {string | undefined} lastValue
 * @param {HTMLElement} parent
 * @param {HTMLCollection} collection
 * @returns {(function(*): (*))|*}
 */
async function choiceChangeListener(evt, setter, lastValue, parent, collection) {
    if (await setter(evt.target.value, parent)) {
        return evt.target.value;
    }

    if (collection.length === 0) {
        return lastValue;
    }

    const attribute = collection[0].tagName === "OPTION"
        ? "selected"
        : "checked";

    for (const element of collection) {
        if (element.value !== lastValue) {
            element[attribute] = false;
            continue;
        }

        element[attribute] = true;
    }

    return lastValue;
}

//* basic inspector components
/**
 * @param {boolean} state
 * @param {Setter<boolean>} setter
 * @param {string} label
 * @returns {HTMLElement}
 */
function CheckboxInspector(state, setter, label = "") {
    const checkbox = (
        Checkbox(label, "i-checkbox", __, {
            listeners: {
                change: async evt => {
                    if (await setter(evt.target.checked, checkbox)) {
                        return;
                    }

                    evt.target.checked = !evt.target.checked;
                }
            }
        })
    );

    if (state) {
        checkbox.querySelector("input").checked = true;
    }

    return checkbox;
}

/**
 * @param {string} title
 * @param {string} className
 * @returns {HTMLElement}
 */
function TitleInspector(title, className = undefined) {
    return (
        Heading(3, "i-title" + (className !== undefined ? " " + className : ""), title)
    );
}

function HRInspector(className = undefined) {
    return (
        Div("i-hr" + (className !== undefined ? " " + className : ""), "​") //todo does not display without zero-width-character (?)
    );
}

/**
 * @typedef KeyValuePair
 * @property {string} text
 * @property {string} value
 * @property {boolean=} selected
 */
/**
 * @param {Setter<string>} setter
 * @param {KeyValuePair[]} radios
 * @param {string} label
 * @returns {HTMLElement}
 */
function RadioGroupInspector(setter, radios, label = undefined) {
    const name = guid(true);
    let lastValue = radios
        .reduce(
            (last, current) =>
                current.selected ? current.value : last,
            undefined
        );

    const radioGroup = (
        Div("i-radio-group", [
            OptionalComponent(label !== undefined,
                Span(__, label)
            ),
            ...radios.map(radio => {
                return (
                    Radio(radio.text, radio.value, name, __,
                        radio.selected !== undefined
                            ? {
                                attributes: {
                                    checked: radio.selected
                                }
                            }
                            : undefined
                    )
                );
            })
        ], {
            listeners: {
                change: async evt => {
                    lastValue = await choiceChangeListener(evt, setter, lastValue, radioGroup, radioGroup.querySelectorAll(`input[name=${name}]`));
                }
            }
        })
    );

    return radioGroup;
}

/**
 * @param {KeyValuePair[]} options
 * @param {string} value
 * @param {string} defaultValue
 * @return {KeyValuePair[]}
 */
function selectOption(options, value, defaultValue = undefined) {
    let defaultOption;
    for (const option of options) {
        if (option.value === defaultValue) {
            defaultOption = option;
        }
        if (option.value !== value) continue;

        option.selected = true;
        return options;
    }

    defaultOption.selected = true;
    return options;
}

/**
 * @template S
 * @param {string} className
 * @param {Setter<S>} setter
 * @param {string} label
 * @param {HTMLElement} component
 * @param {string | undefined} placeholder
 * @param {ComponentOptions} options
 * @returns {HTMLElement}
 */
function LabelAndComponentInspector(className, setter, label, component, placeholder = undefined, options = undefined) {
    const id = guid(true);

    component.id = id;
    if (placeholder) {
        component.setAttribute("placeholder", placeholder);
    }

    return (
        Div(className, [
            OptionalComponent(label !== undefined,
                Label(__, label, {
                    attributes: {
                        for: id
                    }
                })
            ),
            component
        ])
    );
}

/**
 * @param {string | undefined} label
 * @param {HTMLElement} elementFor
 */
function LabelFactory(label, elementFor) {
    if (label === undefined) {
        return;
    }

    const id = guid(true);

    elementFor.id = id;
    return Label(__, label, {
        attributes: {
            for: id
        }
    });
}

/**
 * @param {string | undefined} state
 * @param {Setter<string>} setter
 * @param {string} label
 * @param {string} placeholder
 * @param {ComponentOptions} options
 * @returns {HTMLElement}
 */
function TextAreaInspector(state, setter, label = undefined, placeholder = undefined, options = undefined) {
    const parent = Div("i-text-area");

    options ||= {};
    options.listeners ||= {};
    options.listeners.blur ||= async evt => {
        if (state === evt.target.value) return;
        if (await setter(evt.target.value, parent)) {
            state = evt.target.value;
            return;
        }

        evt.target.value = state ?? "";
    };
    options.listeners.keydown ||= evt => {
        if (!(evt.key === "Enter" && evt.ctrlKey)) return;
        evt.target.dispatchEvent(new Event("blur"));
    };

    options.attributes ||= {};
    if (state) {
        options.attributes.value = state;
    }

    const area = Component("textarea", __, state, options);
    const labelElement = LabelFactory(label, area);

    if (placeholder) {
        area.setAttribute("placeholder", placeholder);
    }

    parent.append(...parseComponentContent([
        labelElement,
        area
    ]));

    return parent;
}

/**
 * @param {string | undefined} state
 * @param {Setter<string>} setter
 * @param {string} label
 * @param {string} placeholder
 * @returns {HTMLElement}
 */
function TextFieldInspector(state, setter, label = undefined, placeholder = undefined) {
    const parent = Div("i-text-field");
    const options = {
        listeners: {
            blur: async evt => {
                if (state === evt.target.value) return;
                if (await setter(evt.target.value, parent)) {
                    state = evt.target.value;
                    return;
                }

                evt.target.value = state ?? "";
            },
            keydown: evt => {
                if (evt.key !== "Enter") return;
                evt.target.dispatchEvent(new Event("blur"));
            }
        },
        attributes: {}
    };

    if (state !== undefined) {
        options.attributes.value = state;
    }

    const textField = Input("text", __, options);
    const labelElement = LabelFactory(label, textField);

    if (placeholder) {
        textField.setAttribute("placeholder", placeholder);
    }

    parent.append(...parseComponentContent([
        labelElement,
        textField
    ]));

    return parent;
}

/**
 * @param {number | undefined} state
 * @param {Setter<string | number>} setter
 * @param {string} label
 * @param {string} placeholder
 * @param {ComponentContent} measurement
 * @returns {HTMLElement}
 */
function NumberInspector(state, setter, label = undefined, placeholder = undefined, measurement = undefined) {
    const id = guid(true);
    const options = {
        attributes: { id }
    };

    if (placeholder) {
        options.attributes.placeholder = placeholder;
    }

    if (state) {
        options.attributes.value = state;
    }

    return (
        Div("i-number", [
            OptionalComponent(label !== undefined,
                Label(__, label, {
                    attributes: {
                        for: id
                    }
                })
            ),
            Input("number", __, options),
            typeof measurement === "string"
                ? Span(__, measurement)
                : measurement
        ])
    );
}

//TODO: create custom date picker
/**
 * @param {Date | undefined} state
 * @param {Setter<Date>} setter
 * @param {string} label
 * @param {boolean} isDateTime
 * @param {string} placeholder
 * @returns {HTMLElement}
 */
function DateInspector(state, setter, label = undefined, isDateTime = false, placeholder = undefined) {
    const input = Input(isDateTime ? "datetime-local" : "date");
    input.addEventListener("change", async () => {
        const date = new Date(input.value);

        if (state === date) return;
        if (await setter(date, input.parentElement)) {
            state = date;
            return;
        }

        input.value = state?.toISOString().slice(0, 16);
    });

    if (state) {
        const timezoneOffset = new Date().getTimezoneOffset() * 60 * 1000;
        const localDateTime = new Date(state.getTime() - timezoneOffset);
        input.value = localDateTime.toISOString().slice(0, 16);
    }

    return (
        LabelAndComponentInspector("i-date", setter, label, input, placeholder)
    );
}

/**
 * @param {Setter<string>} setter
 * @param {KeyValuePair[]} options
 * @param {string} label
 * @param {string} className
 * @returns {HTMLElement}
 */
function SelectInspector(setter, options, label = undefined, className = undefined) {
    const id = guid(true);
    let lastValue = options
        .reduce(
            (last, current) =>
                current.selected ? current.value : last,
            undefined
        );

    const parent = Div("i-select dont-force" + (className !== undefined ? (" " + className) : ""));

    const select = (
        Component("select", __,
            options.map(option =>
                new Option(option.text, option.value, __, option?.selected)
            ), {
                listeners: {
                    change: async evt => {
                        lastValue = await choiceChangeListener(evt, setter, lastValue, parent, select.children);
                    }
                }
            }
        )
    );

    parent.append(...parseComponentContent([
        OptionalComponent(label !== undefined,
            Label(__, label, {
                attributes: {
                    for: id
                }
            })
        ),
        Div("select-container",
            select
        )
    ]));

    return parent;
}

/**
 * @param {string | undefined} state
 * @param {Setter<string>} setter
 * @param {string} label
 * @param {string} placeholder
 * @returns {HTMLElement}
 */
function ColorPickerInspector(state, setter, label = undefined, placeholder = undefined) {
    const options = {
        attributes: {}
    };

    if (state) {
        options.attributes.value = state;
    }

    return (
        LabelAndComponentInspector("i-date", setter, label, Input("color", __, options), placeholder)
    );
}

function NotInspectorAble() {
    // return (
    //   TitleInspector("This element cannot be changed")
    // );
    return undefined;
}



class ColorPicker {
    #rootElement;
    get rootElement() {
        return this.#rootElement;
    }

    #display;
    #red;
    #green;
    #blue;
    #alpha;
    #old;
    #new;

    constructor(inline = false, cancelAction = () => {
    }, pickAction = () => {
    }) {
        this.#rootElement = ColorPicker.createColorPicker(inline);

        this.#red = this.#rootElement.querySelector(".color-picker-r");
        this.#red.addEventListener("input", this.onRGBChange("red"));

        this.#green = this.#rootElement.querySelector(".color-picker-g");
        this.#green.addEventListener("input", this.onRGBChange("green"));

        this.#blue = this.#rootElement.querySelector(".color-picker-b");
        this.#blue.addEventListener("input", this.onRGBChange("blue"));

        this.#alpha = this.#rootElement.querySelector(".color-picker-a");
        this.#alpha.addEventListener("input", this.onRGBChange("alpha"));

        this.#display = this.#rootElement.querySelector(".format");
        this.#display.addEventListener("input", evt => {
            const color = this.parseColor(evt.target.value);
            if (color === null) {
                this.#display.classList.add("invalid");
                return;
            }

            this.#display.classList.remove("invalid");
            this.setNewColor(color);
        });

        this.#old = this.#rootElement.querySelector(".old");
        this.#new = this.#rootElement.querySelector(".new");

        this.#rootElement.querySelector(".cancel")?.addEventListener("click", cancelAction);
        this.#rootElement.querySelector(".pick")?.addEventListener("click", pickAction);
    }

    static createColorPicker(inline = false) {
        const guids = Array(5).fill(null).map(() => guid(true));

        return (
            Div("color-picker", [
                OptionalComponent(inline === false,
                    Div("showcase", [
                        Div("transparent"),
                        Div("old"),
                        Div("new")
                    ])
                ),
                Div("sliders", [
                    Div("row", [
                        Label(__, "R:", { attributes: { for: guids[0] } }),
                        Input("range", "color-picker-r", {
                            attributes: {
                                min: "0",
                                max: "255",
                                value: "0",
                                id: guids[0]
                            }
                        })
                    ]),
                    Div("row", [
                        Label(__, "G:", { attributes: { for: guids[1] } }),
                        Input("range", "color-picker-g", {
                            attributes: {
                                min: "0",
                                max: "255",
                                value: "0",
                                id: guids[1]
                            }
                        })
                    ]),
                    Div("row", [
                        Label(__, "B:", { attributes: { for: guids[2] } }),
                        Input("range", "color-picker-b", {
                            attributes: {
                                min: "0",
                                max: "255",
                                value: "0",
                                id: guids[2]
                            }
                        })
                    ]),
                    Div("row", [
                        Label(__, "A:", { attributes: { for: guids[3] } }),
                        Input("range", "color-picker-a", {
                            attributes: {
                                min: "0",
                                max: "1",
                                step: "0.01",
                                value: "0",
                                id: guids[3]
                            }
                        })
                    ])
                ]),
                Div("row", [
                    Label("format-label", "", { attributes: { for: guids[4] } }),
                    Input("text", "format", { attributes: { id: guids[4] } })
                ]),
                OptionalComponent(inline === false,
                    Div("controls", [
                        Button("button-like-main cancel", "Cancel"),
                        Button("button-like-main pick", "Pick")
                    ])
                )
            ])
        );
    }

    /**
     * @param {Color} color
     */
    static toHEX(color) {
        return "#" + [
            color.red.toString(16),
            color.green.toString(16),
            color.blue.toString(16),
            Math.round(color.alpha * 255).toString(16)
        ]
            .map(channel => (channel.length === 1 ? ("0" + channel) : channel))
            .join("");
    }

    setChannel(channel, value) {
        this.#rootElement.style.setProperty("--" + channel, value);
    }

    onRGBChange(channel) {
        return evt => {
            this.setChannel(channel, evt.target.value);
            this.displayCurrentColor();
            this.#rootElement.dispatchEvent(new CustomEvent("pick", { detail: this.getCurrentColor() }));
        };
    }

    /**
     * @typedef Color
     * @property {number} red
     * @property {number} green
     * @property {number} blue
     * @property {number} alpha
     */
    /**
     * @param {string} colorFormat
     * @return {Color | null}
     */
    parseColor(colorFormat) {
        let values = /^rgba?\((25[0-5]|2[0-4][0-9]|1?[0-9]{1,2}) ?, ?(25[0-5]|2[0-4][0-9]|1?[0-9]{1,2}) ?, ?(25[0-5]|2[0-4][0-9]|1?[0-9]{1,2}) ?,? ?(1|0|0\.[0-9]+)?\)$/.exec(colorFormat);
        if (values !== null) {
            return {
                red: +values[1],
                green: +values[2],
                blue: +values[3],
                alpha: +(values[4] ?? 1)
            };
        }

        switch (colorFormat.length) {
            case 4:
                values = /^#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])$/.exec(colorFormat);
                if (values === null) return null;
                return {
                    red: parseInt(values[1].repeat(2), 16),
                    green: parseInt(values[2].repeat(2), 16),
                    blue: parseInt(values[3].repeat(2), 16),
                    alpha: 1
                };
            case 7:
                values = /^#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/.exec(colorFormat);
                if (values === null) return null;
                return {
                    red: parseInt(values[1], 16),
                    green: parseInt(values[2], 16),
                    blue: parseInt(values[3], 16),
                    alpha: 1
                };
            case 9:
                values = /^#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/.exec(colorFormat);
                if (values === null) return null;
                return {
                    red: parseInt(values[1], 16),
                    green: parseInt(values[2], 16),
                    blue: parseInt(values[3], 16),
                    alpha: parseInt(values[4], 16) / 255
                };
            default:
                return null;
        }
    }

    setOldColor(colorFormat) {
        const color = this.parseColor(colorFormat);
        if (color === null) {
            throw "Unknown color format. Currently supported: RGB, RGBA, HEX(3 letters), HEX (6 letters), HEX + alpha (8 letters)";
        }

        this.#old.style.backgroundColor = `rgba(${color.red}, ${color.green}, ${color.blue}, ${color.alpha})`;
        this.setNewColor(color);
        this.displayCurrentColor();
    }

    setNewFromFormat(colorFormat) {
        const color = this.parseColor(colorFormat);
        if (color === null) {
            throw "Unknown color format. Currently supported: RGB, RGBA, HEX(3 letters), HEX (6 letters), HEX + alpha (8 letters)";
        }

        this.setNewColor(color);
        this.displayCurrentColor();
    }

    setNewColor(color) {
        this.#red.value = color.red;
        this.setChannel("red", color.red);

        this.#green.value = color.green;
        this.setChannel("green", color.green);

        this.#blue.value = color.blue;
        this.setChannel("blue", color.blue);

        this.#alpha.value = color.alpha;
        this.setChannel("alpha", color.alpha);

        this.#rootElement.dispatchEvent(new CustomEvent("pick", { detail: color }));
    }

    getCurrentColor() {
        return {
            red: Number(this.#rootElement.style.getPropertyValue("--red")),
            green: Number(this.#rootElement.style.getPropertyValue("--green")),
            blue: Number(this.#rootElement.style.getPropertyValue("--blue")),
            alpha: Number(this.#rootElement.style.getPropertyValue("--alpha"))
        };
    }

    displayCurrentColor(color = undefined) {
        this.#display.value = ColorPicker.toHEX(color ?? this.getCurrentColor());
    }
}
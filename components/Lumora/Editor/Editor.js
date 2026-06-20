const editor_inspector = $(".table > .inspector");
const editor_nav = $("nav");
const editor_viewport_mount = $(".viewport-mount");
const editor_viewport = $("#viewport", editor_viewport_mount);

lumora_viewport_set(editor_viewport, editor_viewport_resize);
window.addEventListener("resize", lumora_viewport_resize);
window.addEventListener("load", lumora_viewport_resize);

lumora_viewport_onResize(dimensions => {
    lumora_viewport.animate({
        width: dimensions.width + "px",
        height: dimensions.height + "px"
    }, {
        duration: lumora_viewport_resizeAnimationDuration,
        fill: "forwards"
    });
});

lumora_viewport_resize();

function editor_viewport_resize() {
    const box = editor_viewport_mount.getBoundingClientRect();
    const maxWidth = box.width;
    const maxHeight = box.height;

    editor_viewport.className = '';
    editor_viewport.classList.add(lumora_viewport_mode.name);

    const targetWidth = maxHeight * lumora_viewport_mode.aspectRatio;
    const targetHeight = maxWidth / lumora_viewport_mode.aspectRatio;

    lumora_viewport_dimension = {
        targetAspectRatio: lumora_viewport_mode.aspectRatio,
        maxWidth,
        maxHeight,
        targetWidth,
        targetHeight,
        width: std_clamp(300, maxWidth, targetWidth),
        height: Math.min(targetHeight, maxHeight),
    };
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
const editor_widgetSelect = $("#widget-select-mount");
/** @type {HTMLElement} */
let editor_selectedWidget = undefined;
let editor_isInSearchMode = false;
let editor_currentCmd;

/**
 * @param {boolean} isInSearch
 */
function editor_setSearchMode(isInSearch) {
    editor_isInSearchMode = isInSearch;
    editor_widgetSelect.classList.toggle("search-mode", isInSearch);

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
function editor_moveSelection(direction) {
    if (editor_selectedWidget === undefined) {
        const widgetPool = editor_isInSearchMode
            ? editor_widgetSelect.querySelectorAll(".widget-option.search-satisfactory")
            : editor_widgetSelect.querySelectorAll(".widget-option");

        editor_selectedWidget = widgetPool[!direction ? 0 : (widgetPool.length - 1)];
        editor_selectedWidget.classList.add("selected");
        return;
    }

    editor_selectedWidget.classList.remove("selected");

    /** @type {HTMLElement[]} */
    let selectionPool = Array.from(editor_widgetSelect.querySelectorAll(".widget-option"));
    if (direction) {
        selectionPool = selectionPool.reverse();
    }

    let pointer = selectionPool.indexOf(editor_selectedWidget);

    do {
        pointer++;
        if (pointer === selectionPool.length) {
            pointer = 0;
        }

        if (editor_isInSearchMode === false || selectionPool[pointer].classList.contains("search-satisfactory")) {
            editor_selectedWidget = selectionPool[pointer];
            break;
        }
    } while (selectionPool[pointer] !== editor_selectedWidget);

    editor_selectedWidget.classList.add("selected");

    std_dom_scrollIntoView(editor_selectedWidget, editor_widgetSelect);
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



async function editor_loadContent() {
    const dataElement = $("#editor-data");
    let data = dataElement.textContent.trim();
    const root = await WRoot.buildAsync(data, null, true);

    dataElement.remove();

    window.rootWidget = root;
    document.widgetElement = root;

    const vp = document.querySelector("#viewport");
    vp.appendChild(root.rootElement);

    root.rootElement.click();
}

async function editor_loadWidgets() {
    const widgetContainer = $("#widgets");
    const categories = Array.from(widgetContainer.children)
        .filter(x => is(x.dataset.isVisible) && x.dataset.isVisible === "true")
        .reduce((map, x) => {
            const description = {
                class: x.dataset.class,
                isVisible: x.dataset.isVisible === "true",
                name: x.dataset.name,
                category: x.dataset.category,
                icon: $(".widget-icon", x),
            };

            description.searchIndex = description.category + '_' + description.name;

            if (map.has(description.category)) {
                map.get(description.category).push(description);
            } else {
                map.set(description.category, [description]);
            }

            return map;
        }, new Map());

    editor_widgetSelect.textContent = "";
    for (const category of Array.from(categories.keys()).sort()) {
        editor_widgetSelect.appendChild(
            jsml.div("widget-category", [
                jsml.div("label",
                    jsml.h3(_, category)
                ),
                jsml.div("content",
                    categories.get(category).map(x =>
                        jsml.div(
                            {
                                class: "widget-option",
                                "data-search": x.searchIndex,
                                "data-class": x.class,
                                onClick: function () {
                                    if (editor_currentCmd === undefined) {
                                        return;
                                    }

                                    editor_currentCmd.replaceSelf(widgets.get(x.class).default(editor_currentCmd.parentWidget, true));
                                    editor_widgetSelect.style.visibility = "hidden";
                                },
                                onMouseover: function () {
                                    editor_widgetSelect.querySelectorAll(".widget-option").forEach(w => w.classList.remove("selected"));
                                    editor_selectedWidget = this;
                                    this.classList.add("selected");
                                }
                            },
                            [
                                x.icon,
                                jsml.span(_, x.name)
                            ]
                        ))
                )
            ])
        );
    }
}

std_onLoad(() => {
    window.addEventListener("load", async () => {
        await Promise.all([
            editor_loadContent(),
            editor_loadWidgets()
        ])
    }, { once: true });
});



/**
 * @type {HTMLElement | undefined}
 */
let widgetSelectAnchor = undefined;

function editor_unfollowWidgetSelect() {
    widgetSelectAnchor = undefined;
}

lumora_viewport_onResize(() => {
    if (widgetSelectAnchor === undefined || widgetSelectAnchor.parentElement === null || widgetSelectAnchor.parentElement === undefined) return;

    setTimeout(() => {
        const rect = widgetSelectAnchor.getBoundingClientRect();
        editor_widgetSelect.style.left = (rect.x - widgetSelectAnchor.parentElement.getBoundingClientRect().x) + "px";
        editor_widgetSelect.style.top = (widgetSelectAnchor.offsetTop + rect.height) + "px";
    }, 0);
});

/**
 * @param {HTMLElement} to
 */
function editor_moveWidgetSelect(to) {
    if ("widget" in to) {
        editor_currentCmd = to.widget;
    }

    to.scrollIntoView();
    widgetSelectAnchor = to;

    const toBoundingBox = to.getBoundingClientRect();
    editor_widgetSelect.style.left = (toBoundingBox.x - to.parentElement.getBoundingClientRect().x) + "px";
    editor_widgetSelect.style.top = (to.offsetTop + toBoundingBox.height) + "px";
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

    editor_widgetSelect.style.visibility = "visible";
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
    const dropAtContainerName = editor_getClosestByClass(dragHint, "confined-container")?.constructor.name;

    if (dragHint !== null || !(parentWidget === null || parentWidget === undefined)) {
        const toBeMoved = $$("." + WIDGET_SELECTION_CLASS);
        for (const toBeMovedElement of toBeMoved) {
            if (!toBeMovedElement.classList.contains("widget")) continue;
            if (dropAtContainerName !== editor_getClosestByClass(toBeMovedElement, "confined-container", false)?.constructor.name) continue;

            toBeMovedElement.widget.remove(false, false);
            await parentWidget.insertBeforeWidget(toBeMovedElement.widget, dragHint.nextElementSibling?.widget, false);
            dragHint.parentElement.insertBefore(toBeMovedElement, dragHint);
        }
    }

    await cleanUpAfterDrag(evt);
});
document.body.addEventListener("dragend", async evt => {
    await cleanUpAfterDrag(evt);
});
document.body.addEventListener("dragover", evt => {
    if (editor_getClosestByClass(beingDragged, "confined-container", false)?.constructor.name === editor_getClosestByClass(evt.target, "confined-container", false)?.constructor.name) {
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

    await std_wait(10);
    const dragHint = $(".drag-hint");

    if (dragHint === null) return;

    dragHint.classList.remove("expand");
    await std_wait(100);
    dragHint.remove();
}

window.addEventListener("keydown", evt => {
    if (evt.key === "Escape") {
        edit_deselect();
    }

    if ((evt.key === "Delete" || evt.key === "Backspace") && evt.handledAction !== true) {
        edit_delete();
    }
});
window.addEventListener("mousemove", evt => {
    const hoveringOver = editor_getClosestByClass(evt.target, "edit");

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
    if (window.rootWidget === undefined) {
        return;
    }

    edit_deselect();

    if ("page" in window.rootWidget) {
        for (const child of window.rootWidget.page.children) {
            child.select();
        }
    }
}

function edit_deselect() {
    for (const selectedElement of $$("." + WIDGET_SELECTION_CLASS)) {
        selectedElement.classList.remove(WIDGET_SELECTION_CLASS);
    }
}

function edit_delete() {
    for (const widgetElement of $$("." + WIDGET_SELECTION_CLASS)) {
        widgetElement?.widget.remove(true, true);
    }
}

function edit_copy(event) {
    if ("clipboardActionHandled" in event && event.clipboardActionHandled === true) {
        return;
    }

    event.stopPropagation();
    event.preventDefault();

    clipboardBuffer = Array.from($$("." + WIDGET_SELECTION_CLASS))
        .map(element => ({
            element,
            containerName: element?.widget.getClosestConfinedContainer()?.constructor.name
        }));
}

function edit_cut(event) {
    if ("clipboardActionHandled" in event && event.clipboardActionHandled === true) {
        return;
    }

    event.stopPropagation();
    event.preventDefault();

    clipboardBuffer = [];
    for (const element of $$("." + WIDGET_SELECTION_CLASS)) {
        if (element.widget === undefined) continue;

        const containerName = element.widget.getClosestConfinedContainer()?.constructor.name;
        element.classList.remove(WIDGET_SELECTION_CLASS);
        element?.widget.remove(true, true);

        clipboardBuffer.push({ element, containerName });
    }
}

function edit_paste(event) {
    if ("clipboardActionHandled" in event && event.clipboardActionHandled === true) return;

    event.preventDefault();

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

window.addEventListener("cut", edit_cut);
window.addEventListener("copy", edit_copy);
window.addEventListener("paste", edit_paste);


/**
 * @param {HTMLElement} element
 * @param {string} className
 * @param {boolean} includeArgumentElement
 */
function editor_getClosestByClass(element, className, includeArgumentElement = true) {
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

let editor_currentlyInspecting;

/**
 * @param {Content} inspectorHTML
 * @param {Widget} widget
 */
function editor_inspect(inspectorHTML, widget) {
    editor_currentlyInspecting = widget;
    editor_inspector.textContent = "";

    jsml_addContent(editor_inspector, inspectorHTML);
}



async function file_save() {
    const structure = window.rootWidget.save();

    const response = await fetch(window.location, {
        method: "post",
        body: JSON.stringify(structure)
    });

    if (await std_fetch_handleServerError(response)) {
        return;
    }

    if (!response.ok) {
        await window_alert("Website was not saved properly");
        return;
    }

    await window_alert("Website was saved successfully");
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
        .then(() => window_alert("Link copied."));
}

function file_exit() {
    close();
}
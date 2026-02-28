class WRoot extends ContainerWidget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef RootJSONType
     * @property {boolean=} isHeaderIncluded
     * @property {"center" | "start" | "end" | string | null} headerTitleAlign
     * @property {string=} headerTitleColor
     * @property {string=} headerImageSrc
     * @property {string=} headerImageHash
     * @property {string=} headerImageVariant
     * @property {Webpage=} webpage
     * @property {boolean=} areCommentsAvailable
     *
     * @typedef {RootJSONType & WidgetJSON} RootJSON
     */
    /**
     * @typedef Webpage
     * @property {number} ID
     * @property {boolean} isHomePage
     * @property {boolean} isPublic
     * @property {boolean} isTakenDown
     * @property {boolean} isTemplate
     * @property {string} src
     * @property {string} thumbnailSRC
     * @property {string} thumbnail
     * @property {string} timeCreated
     * @property {string} releaseDate
     * @property {string} title
     * @property {number} usersID
     */
    
    /** @type {RootJSON} */
    #json;

    /**
     * @return {RootJSON}
     */
    get json() {
        return this.#json;
    }

    set json(json) {
        this.#json = json;
        this.dispatchJSONEvent();
    }

    /**
     * @param {RootJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(
            jsml.div("w-root"),
            parent,
            editable
        );
        this.removeMargin();

        this.editable = editable;
        this.#json = json;
        // this.#json.webpage = Object.assign({}, webpage);
        // this.#json.webpage.releaseDate = webpage.releaseDate !== undefined
        //     ? new Date(webpage.releaseDate?.replace(" ", "T") + "Z")
        //     : undefined;

        this.header = WHeader.build({
            titleAlign: json.headerTitleAlign,
            titleColor: json.headerTitleColor
        }, this, editable);
        this.page = WPage.build({}, this, editable);
        // this.commentSection = WCommentSection.build({
        //     areCommentsAvailable: json.areCommentsAvailable,
        //     webpageID: webpage.ID,
        //     creatorID: webpage.usersID
        // }, this, editable);

        this.appendWidget(this.header);
        this.appendWidget(this.page);
        // this.appendWidget(this.commentSection);
    }

    #listeners = [];

    /**
     * @param {(json: RootJSON)=>void} callback
     */
    addJSONListener(callback) {
        this.#listeners.push(callback);
    }

    dispatchJSONEvent() {
        for (const listener of this.#listeners) {
            listener(this.#json);
        }
    }

    /**
     * @type {HTMLElement}
     */
    draggingWidget;

    /**
     * @param {RootJSON} json
     * @param {boolean} editable
     * @returns {Promise<WRoot>}
     */
    static async #createRoot(json, editable = false) {
        const root = new WRoot(json, null, editable);

        if (is(json?.children)) {
            for (const child of json.children) {
                // if (child.type === "WCommand") continue;
                await root.page.appendWidget(widgets.get(child.type).build(child, root.page, editable));
            }
        }

        return root;
    }



    /**
     * @param {WidgetJSON} widget
     * @param {Set<string>} importSet
     * @param {boolean} bypassExistsRestriction
     */
    static async walkWStructure(widget, importSet, bypassExistsRestriction = false) {
        if (!widgets.exists(widget.type) || bypassExistsRestriction) {
            importSet.add(widget.type);
        }

        if (widget.children) {
            for (const child of widget.children) {
                importSet = await this.walkWStructure(child, importSet);
            }
        }

        return importSet;
    }



    /**
     * @override
     * @param {string | undefined} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {Promise<Widget>}
     */
    static async buildAsync(json, parent = null, editable = false) {
        let structure;

        try {
            structure = JSON.parse(json);
        } catch (ignored) {
            structure = WRoot.defaultJson();
        }

        return await this.#createRoot(structure, editable);
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {Widget}
     */
    static default(parent, editable = false) {
        return new WRoot(
            WRoot.defaultJson(),
            parent
        );
    }

    static defaultJson() {
        return {
            type: "WRoot",
            children: [],
            areCommentsAvailable: false,
            isHeaderIncluded: false,
            headerTitleAlign: "center",
        };
    }

    /** @type {HTMLElement | undefined} */
    #imageVariantSelect;
    createImageVariantSelect() {
        if (is(this.#imageVariantSelect)) {
            return this.#imageVariantSelect;
        }

        const api = api_loadFileSystem();
        const url = new URL(api.imageVariantUrl);
        url.searchParams.set('name', 'Header image variants');

        this.#imageVariantSelect = Remote('div', url.href, 'Loading image variants...');
        this.#imageVariantSelect.addEventListener('change', event => {
            this.#json.headerImageVariant = event.target.value;
            this.dispatchJSONEvent();
        });

        if (is(this.#json.headerImageVariant)) {
            this.#imageVariantSelect.addEventListener(JSML_EVENT_FETCHED, event => {
                const select = event.detail?.element;
                if (!is(select)) {
                    return;
                }

                form_select_selectOption(select, this.#json.headerImageVariant);
            }, { once: true });
        }

        return this.#imageVariantSelect;
    }

    /**
     * @override
     * @returns {Content}
     */
    get inspectorHTML() {
        const headerTitleColorPicker = new ColorPicker(true);
        const pickerID = std_id_html(8);
        headerTitleColorPicker.rootElement.id = pickerID;
        std_dom_onMount("#" + pickerID)
            .then(() => {
                const formatLabel = headerTitleColorPicker.rootElement.querySelector(".format-label");
                const revertButton = jsml.button(
                    {
                        onClick: () => {
                            headerTitleColorPicker.setNewFromFormat("#000");
                            this.#json.headerTitleColor = undefined;
                            this.dispatchJSONEvent();
                        }
                    },
                    "Revert"
                );

                formatLabel.parentElement.insertBefore(revertButton, formatLabel);
                formatLabel.remove();
            });

        headerTitleColorPicker.setNewFromFormat(this.#json.headerTitleColor ?? "#000000ff");
        headerTitleColorPicker.rootElement.addEventListener("pick", evt => {
            this.#json.headerTitleColor = ColorPicker.toHex(evt.detail);
            this.dispatchJSONEvent();
        });

        const headerSettings = [
            HRInspector(this.#json.isHeaderIncluded ? "" : "display-none"),
            TitleInspector("Header", this.#json.isHeaderIncluded ? "" : "display-none"),
            jsml.div("i-header-settings inner-padding" + (this.#json.isHeaderIncluded ? "" : " display-none"), [
                jsml.div("i-row", [
                    jsml.span(_, "Image"),
                    jsml.div("i-row", [
                        jsml.button({
                            class: "button-like-main",
                            onClick: async () => {
                                this.#json.headerImageSrc = undefined;
                                this.#json.headerImageHash = undefined;
                                this.#json.headerImageVariant = undefined;
                                this.dispatchJSONEvent();
                            }
                        }, "Remove"),
                        jsml.button({
                            class: "button-like-main",
                            onClick: async event => {
                                const api = api_loadFileSystem();
                                if (!is(api)) {
                                    return;
                                }

                                const hash = await window_fileSelect(api.createDirectoryUrl('image'));
                                if (!is(hash)) {
                                    return;
                                }

                                this.#json.headerImageHash = hash;
                                this.dispatchJSONEvent();
                            }
                        }, "Select")
                    ])
                ]),
                this.createImageVariantSelect(),
                // jsml.div("i-row", [
                //     jsml.span(),
                //     Button("button-like-main", "Generate theme", async evt => {
                //         const win = showWindow("theme-creator");
                //         win.dataset.imageSource = this.#json.webpage.thumbnail.substring(0, 10);
                //         win.querySelector("#theme-name").value = "My theme";
                //     })
                // ]),
                RadioGroupInspector(
                    value => {
                        this.#json.headerTitleAlign = value;
                        this.dispatchJSONEvent();
                        return true;
                    },
                    selectOption(
                        [
                            { text: "Left", value: "start" },
                            { text: "Center", value: "center" },
                            { text: "Right", value: "end" }
                        ],
                        this.#json.headerTitleAlign,
                        "center"),
                    "Title align"
                ),
                jsml.div(_, "Title color"),
                headerTitleColorPicker.rootElement
            ])
        ];



        // const releaseDate = DateInspector(
        //     this.json.webpage.releaseDate,
        //     async (value, parentElement) => {
        //         const response = await AJAX.patch("/page/release-date", JSONHandler(), {
        //             body: JSON.stringify({
        //                 id: webpage.ID,
        //                 releaseDate: value.toISOString()
        //             })
        //         });
        //
        //         if (response.error !== undefined || response.rowCount !== 1) {
        //             rejected(parentElement);
        //             console.log(response);
        //             return false;
        //         }
        //
        //         this.#json.webpage.releaseDate = value.toISOString();
        //         this.dispatchJSONEvent();
        //         validated(parentElement);
        //         return true;
        //     },
        //     "Release date",
        //     true
        // );

        // const releaseDateInput = releaseDate.querySelector("input");
        //
        // if (this.#json.webpage.releaseDate === undefined) {
        //     releaseDate.classList.add("display-none");
        // }



        // const themeContent = jsml.div("content");
        // const themeLabel = jsml.span(_, "Loading theme...");
        //
        // const themeSelect = jsml.div("select-dropdown", [
        //     jsml.div("label", [
        //         themeLabel,
        //         SVG("icon-arrow", "icon")
        //     ]),
        //     themeContent
        // ], {
        //     attributes: { style: "--height: 200px" },
        //     listeners: {
        //         click: evt => {
        //             themeSelect.classList.toggle("expand");
        //             evt.stopImmediatePropagation();
        //         }
        //     }
        // });
        //
        // const themeRemover = jsml.div("i-row display-none", [
        //     jsml.span(),
        //     jsml.div("i-row", [
        //         Button("button-like-main", "Rename", async () => {
        //             if (themeRemover.dataset.themeSRC === "") return;
        //
        //             const win = showWindow("theme-rename", false);
        //             win.querySelector("#theme-rename-field").name = themeRemover.dataset.name;
        //             win.dataset.src = themeRemover.dataset.themeSRC;
        //         }),
        //         Button("button-like-main", "Remove", async () => {
        //             if (themeRemover.dataset.themeSRC === "" || !confirm("Do you want to remove current theme?")) return;
        //
        //             const response = await AJAX.delete(`/theme/${themeRemover.dataset.themeSRC}`, JSONHandler());
        //
        //             if (response.error) {
        //                 console.log(response);
        //                 return;
        //             }
        //
        //             Theme.reset();
        //             inspect(this.inspectorHTML, this);
        //         })
        //     ])
        // ]);
        //
        // Theme.get("/theme/website/" + webpage.src)
        //     .then(async theme => {
        //         if (!(theme.src.length === 9 && theme.src[0] === "_")) {
        //             themeRemover.classList.remove("display-none");
        //             themeRemover.dataset.themeSRC = theme.src;
        //         } else {
        //             themeRemover.classList.add("display-none");
        //             themeRemover.dataset.themeSRC = "";
        //         }
        //
        //         const usersThemes = await Theme.getUsers("/theme/user/all-v2");
        //
        //         themeContent.append(
        //             ...usersThemes
        //                 .map(theme => Theme.createColor(theme, themeSelect))
        //         );
        //
        //         for (const themeOption of themeContent.children) {
        //             if (themeOption.dataset.value !== theme.src) continue;
        //
        //             themeSelect.value = themeOption.dataset.value;
        //             themeLabel.innerText = themeOption.innerText;
        //             themeRemover.dataset.name = themeOption.innerText;
        //             break;
        //         }
        //
        //         themeSelect.addEventListener("change", async () => {
        //             const themeChangeResponse = AJAX.patch("/page/", JSONHandler(), {
        //                 body: JSON.stringify({
        //                     id: webpage.ID,
        //                     property: "themesSRC",
        //                     value: themeSelect.dataset.value.length === 9 && themeSelect.dataset.value[0] === "_"
        //                         ? themeSelect.dataset.value.substring(1)
        //                         : themeSelect.dataset.value
        //                 })
        //             });
        //
        //             if (themeChangeResponse.error !== undefined) {
        //                 console.log(themeChangeResponse);
        //                 return;
        //             }
        //
        //             validated(themeSelect);
        //
        //             if (!(themeSelect.dataset.value.length === 9 && themeSelect.dataset.value[0] === "_")) {
        //                 themeRemover.classList.remove("display-none");
        //                 themeRemover.dataset.themeSRC = themeSelect.dataset.value;
        //             } else {
        //                 themeRemover.classList.add("display-none");
        //                 themeRemover.dataset.themeSRC = "";
        //             }
        //             await Theme.setAsLink(themeSelect.dataset.value);
        //
        //             for (const themeOption of themeContent.children) {
        //                 if (themeOption.dataset.value === themeSelect.dataset.value) {
        //                     themeLabel.innerText = themeOption.innerText;
        //                     themeRemover.dataset.name = themeOption.innerText;
        //                     break;
        //                 }
        //             }
        //         });
        //     });

        // if (this.#hasAddedThemeSelectShrinkListener === false) {
        //     window.addEventListener("click", () => themeSelect.classList.remove("expand"));
        //     this.#hasAddedThemeSelectShrinkListener = true;
        // }

        return [
            TitleInspector("Website"),

            HRInspector(),

            // TitleInspector("Visibility"),
            // RadioGroupInspector(async (value, parentElement) => {
            //     if (value === "planned") {
            //         const releaseDateString = new Date(releaseDateInput.value === "" ? Date.now() : releaseDateInput.value).toISOString();
            //         const plannedResponse = await AJAX.patch("/page/visibility/planned", JSONHandler(), {
            //             body: JSON.stringify({
            //                 id: webpage.ID,
            //                 releaseDate: releaseDateString
            //             })
            //         });
            //
            //         if (plannedResponse.error || plannedResponse.rowCount !== 1) {
            //             rejected(parentElement);
            //             return false;
            //         }
            //
            //         validated(parentElement);
            //         releaseDate.classList.remove("display-none");
            //         this.#json.webpage.releaseDate = new Date(releaseDateInput.value === "" ? Date.now() : releaseDateInput.value).toISOString();
            //         this.dispatchJSONEvent();
            //         return true;
            //     }
            //
            //     const visibilityResponse = await AJAX.patch(`/page/visibility/${value}`, JSONHandler(), {
            //         body: JSON.stringify({
            //             id: webpage.ID
            //         })
            //     });
            //
            //     if (visibilityResponse.error) {
            //         rejected(parentElement);
            //         console.log(visibilityResponse);
            //         return false;
            //     }
            //
            //     validated(parentElement);
            //     releaseDate.classList.add("display-none");
            //     this.#json.webpage.releaseDate = undefined;
            //     this.#json.webpage.isPublic = value === "public";
            //     this.dispatchJSONEvent();
            //     return true;
            // }, selectOption([
            //     { text: "Public", value: "public" },
            //     { text: "Private", value: "private" },
            //     { text: "Planned", value: "planned" }
            // ], this.#json.webpage.isPublic
            //     ? "public"
            //     : this.#json.webpage.releaseDate !== undefined
            //         ? "planned"
            //         : "private")),
            // releaseDate,
            //
            // HRInspector(),

            TitleInspector("Properties"),
            // TextFieldInspector(this.#json.webpage.title, async (value, parent) => {
            //     if (value.length < 1 || value.length > 64) {
            //         rejected(parent);
            //         return false;
            //     }
            //
            //     const response = await AJAX.patch("/page/", JSONHandler(), {
            //         body: JSON.stringify({
            //             id: webpage.ID,
            //             property: "title",
            //             value
            //         })
            //     });
            //
            //     if (response.error !== undefined) {
            //         rejected(parent);
            //         console.log(response);
            //         return false;
            //     }
            //
            //     validated(parent);
            //     this.#json.webpage.title = value;
            //     this.dispatchJSONEvent();
            //
            //     return true;
            // }, "Title"),
            // CheckboxInspector(this.#json.webpage.isHomePage, async (value, parentElement) => {
            //     const setHomePageResponse = await AJAX.patch(`/page/home-page/${webpage.ID}/${Number(value)}`, JSONHandler());
            //     if (setHomePageResponse.error !== undefined) {
            //         console.log(setHomePageResponse);
            //         rejected(parentElement);
            //         return false;
            //     }
            //
            //     this.#json.webpage.isHomePage = value;
            //     validated(parentElement);
            //
            //     return true;
            // }, "Set as home"),
            // CheckboxInspector(this.#json.areCommentsAvailable, (value) => {
            //     this.#json.areCommentsAvailable = value;
            //     this.dispatchJSONEvent();
            //     return true;
            // }, "Enable comments"),
            CheckboxInspector(this.#json.isHeaderIncluded, value => {
                this.#json.isHeaderIncluded = value;
                this.dispatchJSONEvent();

                headerSettings.forEach(element => element.classList.toggle("display-none", !value));
                return true;
            }, "Include Header"),

            ...headerSettings,

            HRInspector(),

            // TitleInspector("Theme"),
            // themeSelect,
            // themeRemover,

            // HRInspector()

            // jsml.div("i-controls-row", [
            //   Button("button-like-main", "Change"),
            //   Button("button-like-main", "Delete"),
            // ]),
        ];
    }

    #hasAddedThemeSelectShrinkListener = false;

    /**
     * @override
     * @returns {RootJSON}
     */
    save() {
        const {
            isHeaderIncluded,
            areCommentsAvailable,
            headerTitleAlign,
            headerTitleColor,
            headerImageSrc,
            headerImageHash,
            headerImageVariant
        } = this.#json;

        return {
            type: "WRoot",
            isHeaderIncluded,
            areCommentsAvailable,
            headerTitleAlign,
            headerTitleColor,
            headerImageSrc,
            headerImageHash,
            headerImageVariant,
            children: this.page.saveChildren()
        };
    }

    allowsDragAndDrop() {
        return false;
    }

    isSelectAble() {
        return false;
    }

    isSelectionPropagable() {
        return false;
    }

    /** @override */
    remove(doRemoveFromRootElement, doAnimate) {
        console.error("WRoot cannot be removed.");
    }
}



widgets.define("WRoot", WRoot);
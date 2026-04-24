const BOOKMARKS_BUILDER = 'bookmarks';

const BOOKMARKS_KEY = 'startuh_bookmarks';



class BookmarksWidget extends StartuhWidget {
    /** @type {BookmarksWidgetConfig} */
    #config;
    /** @type {HTMLElement} */
    #display;



    constructor(config) {
        super();

        this.#config = config;
        this.setConfig(config);

        this.#display = jsml.div("w-bookmarks");
    }



    renderItems() {
        this.#display.textContent = "";

        if (!is(this.#config.items)) {
            return;
        }

        for (const item of this.#config.items) {
            this.#display.append(
                jsml.a(
                    {
                        class: "bookmark center " + (item.isTitleIcon ? "icon" : ""),
                        href: item.link,
                        style: {
                            backgroundColor: item.color
                        }
                    },
                    item.isTitleIcon
                        ? Icon(item.title)
                        : jsml.span(_, item.title)
                )
            );
        }
    }

    instantiate() {
        this.renderItems();
        const widget = startuh_WidgetElement(this, this.#display, this.#config);
        widget.style.borderRadius = "calc(3 * var(--border-radius))";
        return widget;
    }

    /**
     * @param {Opt<Bookmark>} item
     * @returns {Promise<Opt<Bookmark>>}
     */
    inspectItem(item = undefined) {
        return new Promise(resolve => {
            let result = is(item)
                ? { ...item }
                : {
                    color: "rgba(0, 0, 0, 0)",
                    isTitleIcon: false,
                    title: "",
                    link: ""
                };

            let ret = undefined;

            const colorPicker = new ColorPicker(true);
            colorPicker.rootElement.addEventListener("pick", evt => {
                result.color = ColorPicker.toHex(evt.detail);
            });

            if (is(item)) {
                colorPicker.setNewFromFormat(item.color);
            }

            const w = window_create(
                "Bookmark",
                jsml.div("text-window", [
                    CheckboxInspector(item?.isTitleIcon ?? false, value => {
                        result.isTitleIcon = value;
                        return true;
                    }, "Is title icon"),

                    TextFieldInspector(item?.title ?? "", value => {
                        result.title = value.trim();
                        return true;
                    }, "Title"),

                    TextFieldInspector(item?.link ?? "", value => {
                        result.link = value.trim();
                        return true;
                    }, "Link"),

                    colorPicker.rootElement,

                    jsml.div("controls", [
                        jsml.button({
                            onClick: async () => {
                                if (result.link === "" || result.title === "") {
                                    await window_alert("Title and link must be set", WINDOW_ALERT_SETTINGS);
                                    return;
                                }

                                ret = result;
                                window_close(w);
                            }
                        }, 'Ok'),

                        jsml.button({
                            onClick: () => {
                                window_close(w);
                            }
                        }, 'Cancel'),
                    ])
                ]),
                {
                    isDialog: true,
                    isDraggable: true,
                    isMinimizable: false
                }
            );

            w.addEventListener(EVENT_WINDOW_CLOSED, () => resolve(ret));
            window_open(w);
        });
    }

    inspect() {
        const items = jsml.div("bookmarks-listing");
        let selected = undefined;
        let selectedBookmark = undefined;
        let selectedIndex = undefined;

        const deselect = () => {
            if (is(selected)) {
                selected.classList.remove("selected");
            }

            selected = undefined;
            selectedBookmark = undefined;
            selectedIndex = undefined;
        }

        this.#config.items ??= [];
        for (let i = 0; i < this.#config.items.length; i++) {
            const x = this.#config.items[i];

            const item = jsml.div({
                class: "bookmark",
                onClick: () => {
                    const isSelected = item.classList.contains("selected");
                    deselect();

                    if (isSelected) {
                        return;
                    }

                    item.classList.add("selected");

                    selected = item;
                    selectedBookmark = x;
                    selectedIndex = i;
                }
            }, x.title);

            items.append(item);
        }

        return [
            TitleInspector("Bookmarks"),

            HRInspector(),

            jsml.div("i-bookmarks", [
                items,
                jsml.div('row', [
                    jsml.button({
                        onClick: async () => {
                            const bookmark = await this.inspectItem();
                            if (!is(bookmark)) {
                                return;
                            }

                            this.#config.items.push(bookmark);

                            startuh_save();
                            startuh_inspect(this.inspect());
                            this.renderItems();
                        }
                    }, "Add"),
                    jsml.button({
                        onClick: async () => {
                            if (!is(selected)) {
                                return;
                            }

                            const bookmark = await this.inspectItem(this.#config.items[selectedIndex]);
                            if (!is(bookmark)) {
                                return;
                            }

                            this.#config.items[selectedIndex] = bookmark;

                            startuh_save();
                            startuh_inspect(this.inspect());
                            this.renderItems();
                        }
                    }, "Edit"),
                    jsml.button({
                        onClick: async () => {
                            if (!is(selectedIndex)) {
                                return;
                            }

                            if (!(await window_confirm(`Do you want to delete bookmark '${selectedBookmark.link}'?`, WINDOW_CONFIRM_SETTINGS))) {
                                return;
                            }

                            this.#config.items.splice(selectedIndex, 1);

                            startuh_save();
                            startuh_inspect(this.inspect());
                            this.renderItems();
                        }
                    }, "Delete"),
                ])
            ])
        ];
    }

    save() {
        const ret = {
            ...this.#config,
            ...super.save()
        };

        ret.builder = BOOKMARKS_BUILDER;

        return ret;
    }
}



const bookmarks_builder = new FunctionalStartuhBuilder(
    BOOKMARKS_BUILDER,
    config => new BookmarksWidget(config)
);



startuh_addBuilder(bookmarks_builder);
startuh_addPrefab(
    startuh_PrefabElement(bookmarks_builder, Icon("nf-md-book"), "Bookmarks")
);

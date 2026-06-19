class WCommand extends Widget { // var is used because it creates reference on globalThis (window) object

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef CommandJSONType
     * @param {string} text
     *
     * @typedef {CommandJSONType & WidgetJSON} CommandJSON
     */

    /**
     * @param {CommandJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        super(jsml.span("w-command show-hint", json?.text), parent, editable);
        this.childSupport = "none";

        if (editable === false) {
            return;
        }

        this.rootElement.setAttribute("contenteditable", "true");
        this.rootElement.setAttribute("spellcheck", "false");

        let doRemoveOnNextDelete = true;

        this.rootElement.addEventListener("keyup", evt => {
            if (doRemoveOnNextDelete === true && (evt.key === "Backspace" || evt.key === "Delete")) {
                this.remove();
                //widgetSelect.style.visibility = "hidden";
                //setSearchMode(false);
            }

            if (this.rootElement.textContent === "") {
                this.rootElement.classList.add("show-hint");
                editor_widgetSelect.style.visibility = "hidden";
                //setSearchMode(false);
                doRemoveOnNextDelete = true;

                return;
            }

            doRemoveOnNextDelete = false;
            this.rootElement.classList.remove("show-hint");

            if (this.rootElement.textContent === "/" && evt.key === "/") {
                editor_moveWidgetSelect(this.rootElement);
                editor_setSearchMode(false);
                return;
            }

            const parsedCommand = WCommand.#parseCommand(this.rootElement.textContent);

            if (parsedCommand === "") {
                editor_setSearchMode(false);
                return;
            }

            editor_setSearchMode(true);
            let satisfactoryCountGlobal = 0;
            let satisfactoryWidgetGlobal = undefined;
            for (const category of editor_widgetSelect.children) {
                let satisfactoryCount = 0;

                for (const widget of category.children[1].children) {
                    if (!WCommand.#satisfiesSearch(widget.dataset.search, parsedCommand)) {
                        widget.classList.remove("search-satisfactory");
                        continue;
                    }

                    widget.classList.add("search-satisfactory");
                    satisfactoryCount++;
                    satisfactoryWidgetGlobal = widget;
                    satisfactoryCountGlobal++;
                }

                if (satisfactoryCount !== 0) {
                    category.classList.remove("not-search-satisfactory");
                    continue;
                }

                category.classList.add("not-search-satisfactory");
            }

            if (satisfactoryCountGlobal === 0) {
                editor_widgetSelect.classList.add("no-results");
            } else {
                editor_widgetSelect.classList.remove("no-results");
                if (satisfactoryCountGlobal === 1) {
                    editor_widgetSelect.querySelectorAll(".widget-option").forEach(w => w.classList.remove("selected"));

                    editor_selectedWidget = satisfactoryWidgetGlobal;
                    editor_selectedWidget.classList.add("selected");
                }
            }
        });

        this.rootElement.addEventListener("keydown", async event => {
            if (event.key === "ArrowUp") {
                editor_moveSelection(true);
                event.preventDefault();
                return;
            }

            if (event.key === "ArrowDown") {
                editor_moveSelection(false);
                event.preventDefault();
                return;
            }

            if (event.key === "Enter") {
                if (this.rootElement.textContent[0] === "/") {
                    event.preventDefault();

                    if (editor_selectedWidget === undefined) {
                        return;
                    }

                    if (!editor_selectedWidget.classList.contains("search-satisfactory") && editor_isInSearchMode) {
                        return;
                    }

                    const defaultWidget = widgets.get(editor_selectedWidget.dataset.class).default(this.parentWidget, true);
                    await this.parentWidget.insertBeforeWidget(defaultWidget, this);
                    editor_unfollowWidgetSelect();
                    this.remove();

                    editor_setSearchMode(false);
                    editor_widgetSelect.style.visibility = "hidden";
                    defaultWidget.focus();
                    defaultWidget.rootElement.scrollIntoView({ behavior: "smooth" });

                    return;
                }

                event.preventDefault();

                const lines = [this.rootElement.textContent];
                if (this.rootElement.textContent !== "") {
                    lines.push("");
                }

                const textWidget = WText.build({
                    type: "WText",
                    textEditor: {
                        content: lines,
                        mode: "simple"
                    }
                }, this.parentWidget, true);

                await this.parentWidget.insertBeforeWidget(textWidget, this);
                editor_unfollowWidgetSelect();
                this.remove();

                textWidget.focus();
                textWidget.rootElement.scrollIntoView({ behavior: "smooth" });
            }
        });
    }

    /**
     * @param {string} command
     */
    static #parseCommand(command) {
        return command.substring(1);
    }


    /**
     * @param {string} index
     * @param {string} query
     * @returns {boolean}
     */
    static #satisfiesSearch(index, query) {
        query = query.toLowerCase();

        let queryPointer = 0;
        for (const char of index.toLowerCase()) {
            if (char === query[queryPointer]) {
                queryPointer++;
            }

            if (queryPointer === query.length) {
                return true;
            }
        }

        return false;
    }


    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WCommand}
     */
    static default(parent, editable = false) {
        return new WCommand({}, parent, editable);

        // command.rootElement.setAttribute("contenteditable", "true");
        // command.rootElement.setAttribute("spellcheck", "false");
        //
        // let doRemoveOnNextDelete = true;
        //
        // command.rootElement.addEventListener("keyup", evt => {
        //   if (doRemoveOnNextDelete === true && (evt.key === "Backspace" || evt.key === "Delete")) {
        //     command.remove();
        //     //widgetSelect.style.visibility = "hidden";
        //     //setSearchMode(false);
        //   }
        //
        //   if (command.rootElement.textContent === "") {
        //     command.rootElement.classList.add("show-hint");
        //     widgetSelect.style.visibility = "hidden";
        //     //setSearchMode(false);
        //     doRemoveOnNextDelete = true;
        //
        //     return;
        //   }
        //
        //   doRemoveOnNextDelete = false;
        //   command.rootElement.classList.remove("show-hint");
        //
        //   if (command.rootElement.textContent === "/" && evt.key === "/") {
        //     moveWidgetSelect(command.rootElement);
        //     setSearchMode(false);
        //     return;
        //   }
        //
        //   const parsedCommand = this.#parseCommand(command.rootElement.textContent);
        //
        //   if (parsedCommand === "") {
        //     setSearchMode(false);
        //     return;
        //   }
        //
        //   setSearchMode(true);
        //   let satisfactoryCountGlobal = 0;
        //   let satisfactoryWidgetGlobal = undefined;
        //   for (const category of widgetSelect.children) {
        //     let satisfactoryCount = 0;
        //
        //     for (const widget of category.children[1].children) {
        //       if (!this.#satisfiesSearch(widget.dataset.search, parsedCommand)) {
        //         widget.classList.remove("search-satisfactory");
        //         continue;
        //       }
        //
        //       widget.classList.add("search-satisfactory");
        //       satisfactoryCount++;
        //       satisfactoryWidgetGlobal = widget;
        //       satisfactoryCountGlobal++;
        //     }
        //
        //     if (satisfactoryCount !== 0) {
        //       category.classList.remove("not-search-satisfactory");
        //       continue;
        //     }
        //
        //     category.classList.add("not-search-satisfactory");
        //   }
        //
        //   if (satisfactoryCountGlobal === 0) {
        //     widgetSelect.classList.add("no-results");
        //   } else {
        //     widgetSelect.classList.remove("no-results");
        //     if (satisfactoryCountGlobal === 1) {
        //       widgetSelect.querySelectorAll(".widget-option").forEach(w => w.classList.remove("selected"));
        //
        //       selectedWidget = satisfactoryWidgetGlobal;
        //       selectedWidget.classList.add("selected");
        //     }
        //   }
        //
        // });
        //
        // command.rootElement.addEventListener("keydown", evt => {
        //   if (evt.key === "ArrowUp") {
        //     moveSelection(true);
        //     evt.preventDefault();
        //     return;
        //   }
        //
        //   if (evt.key === "ArrowDown") {
        //     moveSelection(false);
        //     evt.preventDefault();
        //     return;
        //   }
        //
        //   if (evt.key === "Enter") {
        //     if (command.rootElement.textContent[0] === "/") {
        //       evt.preventDefault();
        //
        //       if (selectedWidget === undefined) {
        //         return;
        //       }
        //
        //       if (!selectedWidget.classList.contains("search-satisfactory") && isInSearchMode) {
        //         return;
        //       }
        //
        //       const defaultWidget = widgets.get(selectedWidget.dataset.class).default(command.parentWidget, true);
        //       command.parentWidget.insertBeforeWidget(defaultWidget, command);
        //       unfollowWidgetSelect();
        //       command.remove();
        //
        //       setSearchMode(false);
        //       widgetSelect.style.visibility = "hidden";
        //       defaultWidget.focus();
        //       defaultWidget.rootElement.scrollIntoView({behavior: "smooth"});
        //
        //       return;
        //     }
        //
        //     evt.preventDefault();
        //
        //     const lines = [command.rootElement.textContent];
        //     if (command.rootElement.textContent !== "") {
        //       lines.push("");
        //     }
        //
        //     const textWidget = WText.build({
        //       type: "WText",
        //       textEditor: {
        //         content: lines,
        //         mode: "simple"
        //       }
        //     }, command.parentWidget, true);
        //
        //     command.parentWidget.insertBeforeWidget(textWidget, command);
        //     unfollowWidgetSelect();
        //     command.remove();
        //
        //     textWidget.focus();
        //     textWidget.rootElement.scrollIntoView({behavior: "smooth"});
        //   }
        // });
        //
        // return command;
    }

    /**
     * @override
     * @param {CommandJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WCommand | WText}
     */
    static build(json, parent, editable = false) {
        if (editable === false) {
            return WText.build({
                type: "WText",
                textEditor: {
                    content: [json.text ?? ""],
                    mode: "simple"
                }
            }, parent, false);
        }

        return new WCommand(json, parent, true);
    }

    /**
     * @override
     * @returns {Content}
     */
    get inspectorHTML() {
        return (
            NotInspectorAble()
        );
    }

    /**
     * @override
     * @returns {WidgetJSON}
     */
    save() {
        return {
            type: "WCommand",
            text: this.rootElement.textContent
        };
    }

    /**
     * @override
     */
    appendEditGui() {
        console.error("Can not add edit GUI to WCommand, because this object will not be saved.");
    }

    isSelectAble() {
        return false;
    }

    isSelectionPropagable() {
        return false;
    }

    focus() {
    }
}



widgets.define("WCommand", WCommand);
const WIDGET_SELECTION_CLASS = "selected-widget-for-operation";



class Observable {
    #value;

    /**
     * @template V
     * @param {V} defaultValue
     */
    constructor(defaultValue) {
        this.#value = defaultValue;
    }

    /**
     * @return {V}
     */
    get value() {
        return this.#value;
    }

    /**
     * @param {V} newValue
     */
    set value(newValue) {
        this.#value = newValue;
        this.dispatch();
    }

    /**
     * @param {string | number} key Ability to address indexes in array with number as a key
     * @param {any} value
     */
    setPropertySafe(key, value) {
        this.#value[key] = value;
    }

    /**
     * Setting property like so will not trigger on change event
     *
     * @param {string | number} key Ability to address indexes in array with number as a key
     * @param {any} value
     */
    setProperty(key, value) {
        this.setPropertySafe(key, value);
        this.dispatch();
    }

    /**
     * @template O
     * @callback ObserverCallback
     * @param {O} value
     */
    /**
     * @type {ObserverCallback<V>[]}
     */
    #listeners = [];

    /**
     * @param {ObserverCallback<V>} listener
     */
    onChange(listener) {
        this.#listeners.push(listener);
        return (() => this.removeListener(listener)).bind(this);
    }

    /**
     * Setting value this way won't trigger change event.
     *
     * Helpful for setting different value in callback `onChange`
     * @param {V} value
     */
    setValueSafe(value) {
        this.#value = value;
    }

    /**
     * @param listener
     */
    removeListener(listener) {
        this.#listeners.splice(this.#listeners.indexOf(listener), 1);
    }

    dispatch() {
        for (const listener of this.#listeners) {
            listener(this.#value);
        }
    }
}



/**
 * @typedef WidgetJSON
 * @property {string=} type
 * @property {Object.<string, string>=} style
 * @property {WidgetJSON[]=} children
 */
class Widget {
    /**
     * @typedef {"none"|"multiple"|number} WidgetChildSupport
     */

    /** @type {WidgetChildSupport} */
    #childSupport = "multiple";

    /** @param {WidgetChildSupport} value */
    set childSupport(value) {
        this.#childSupport = value;
    }

    get childSupport() {
        return this.#childSupport;
    }

    /**
     * @param {HTMLElement} root
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(root, parent, editable = false) {
        this.setRootElement(root);
        this.editable = editable;

        this.parentWidget = parent;

        /** @type {Widget[]} */
        this.children = [];
    }

    setRootElement(element) {
        this.rootElement = element;
        this.rootElement.widget = this;
        this.rootElement.classList.add("widget", "margin");

        if (window.editor_inspect !== undefined) {
            this.rootElement.addEventListener("click", this.inspectHandler.bind(this));
        }

        this.rootElement.addEventListener("click", this.selectHandler.bind(this));
    }

    selectHandler(evt) {
        if (!(evt.ctrlKey && this.editable === true)) {
            return;
        }

        evt.stopPropagation();
        this.toggleSelect();

        if (this.rootElement.classList.contains(WIDGET_SELECTION_CLASS)) {
            for (const child of this.children) {
                child.removeSelect();
            }
        }
    }

    /**
     * @param {Event} evt
     */
    inspectHandler(evt) {
        if (editor_currentlyInspecting === this) {
            evt.stopInspector = true;
            return;
        }

        const inspectorHTML = this.inspectorHTML;
        if (inspectorHTML === NotInspectorAble() || evt.stopInspector === true || editor_currentlyInspecting === this) {
            return;
        }

        editor_inspect(inspectorHTML, this);
        evt.stopInspector = true;
        // evt.stopPropagation();
    }

    actionHandler(evt) {
        evt.handledAction = true;
    }

    /**
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {Widget}
     */
    static default(parent, editable) {
        return new Widget(document.createElement("div"), parent);
    }

    /**
     * @param {WidgetJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {Widget}
     */
    static build(json, parent, editable = false) {
        return new Widget(document.createElement("div"), parent);
    }

    /**
     * @returns {Content}
     */
    get inspectorHTML() {
        return NotInspectorAble();
    }

    /**
     * @return {WRoot | Widget}
     */
    getRoot() {
        let widget = this;
        while (widget.parentWidget !== null && widget.parentWidget !== undefined) {
            widget = widget.parentWidget;
        }
        return widget;
    }

    /**
     * @returns {WidgetJSON}
     */
    save() {
        return {
            type: "Widget"
        };
    }

    focus() {
    }

    /**
     * @returns {WidgetJSON[]}
     */
    saveChildren() {
        return this.children
            .reduce((accumulator, current) => {
                accumulator.push(current.save());
                return accumulator;
            }, []);
    }

    /**
     * @param {Widget} child
     * @param {number} insertionIndex
     */
    childAdded(child, insertionIndex) {
    }

    /**
     * @param {Widget} child
     * @param {number} beforeRemovalIndex
     */
    childRemoved(child, beforeRemovalIndex) {
    }

    removeInspectHandler() {
        if (window.editor_inspect === undefined) return;
        this.rootElement.removeEventListener("click", this.inspectHandler);
    }

    removeMargin() {
        this.rootElement.classList.remove("margin");
    }

    remove(doRemoveFromRootElement = true, doAnimate = true) {
        return this.parentWidget.removeWidget(this, doRemoveFromRootElement, doAnimate);
    }

    /**
     * @param {Widget} replacement
     */
    replaceSelf(replacement) {
        this.parentWidget.replaceWidget(this, replacement);
    }

    /**
     * @param {Widget} widget
     * @param {boolean} doRemoveFromRootElement
     * @param {boolean} doAnimate
     * @returns {Promise<boolean> | boolean}
     */
    removeWidget(widget, doRemoveFromRootElement = true, doAnimate = true) {
        if (doAnimate !== true) {
            return this.#removeWidget(widget, doRemoveFromRootElement);
        }

        widget.rootElement.classList.add("remove");
        return std_wait(225)
            .then(() => this.#removeWidget(widget, doRemoveFromRootElement));
    }

    #removeWidget(widget, doRemoveFromRootElement) {
        if (doRemoveFromRootElement === true) {
            widget.rootElement.remove();
        }

        widget.parentWidget = null;
        const index = this.children.indexOf(widget);

        this.children.splice(index, 1);
        this.childRemoved(widget, index);
        return true;
    }

    /**
     * @param {Widget} find
     * @param {Widget} replacement
     */
    replaceWidget(find, replacement) {
        find.rootElement.parentElement.insertBefore(replacement.rootElement, find.rootElement);
        find.rootElement.remove();

        find.parentWidget = null;
        replacement.parentWidget = this;
        const index = this.children.indexOf(find);

        this.children.splice(index, 1, replacement);
        this.childRemoved(find, index);
        this.childAdded(replacement, index);
    }

    /**
     * @param {Widget | Promise<Widget>} widget
     * @param {boolean} doAppendToRootElement
     */
    #append(widget, doAppendToRootElement = true) {
        if (this.childSupport === "none" || this.children.length === this.childSupport) {
            console.warn("Trying to add child to parent, who does not accept any more children.");
            return;
        }

        widget.parentWidget = this;
        this.children.push(widget);
        this.childAdded(widget, this.children.length - 1);

        if (doAppendToRootElement) {
            this.rootElement.appendChild(widget.rootElement);
        }
    }

    /**
     * @param {Widget | Promise<Widget>} widget
     * @param {boolean} doAppendToRootElement
     */
    appendWidget(widget, doAppendToRootElement = true) {
        if (widget instanceof Promise) {
            return new Promise(resolve => {
                widget.then(w => {
                    this.#append(w, doAppendToRootElement);
                    resolve();
                });
            });
        }

        this.#append(widget, doAppendToRootElement);
    }

    /**
     * @param {Widget} widget
     * @param {Widget=} child
     * @param {boolean=} doInsertToRootElement
     */
    insertBeforeWidget(widget, child = null, doInsertToRootElement = true) {
        if (widget instanceof Promise) {
            return new Promise(resolve => {
                widget.then(w => {
                    this.#insertBefore(w, child, doInsertToRootElement);
                    resolve();
                });
            });
        }

        this.#insertBefore(widget, child, doInsertToRootElement);
    }

    /**
     * @param {Widget} widget
     * @param {Widget} child
     * @param {boolean} doInsertToRootElement
     */
    #insertBefore(widget, child = null, doInsertToRootElement = true) {
        const index = this.children.indexOf(child);

        if (index === -1) {
            this.#append(widget, doInsertToRootElement);
            return;
        }

        if (doInsertToRootElement === true) {
            this.rootElement.insertBefore(widget.rootElement, child.rootElement);
        }

        widget.parentWidget = this;
        this.children.splice(index, 0, widget);
        this.childAdded(widget, index);
    }

    appendEditGui() {
        this.rootElement.style.position = "relative";
        this.rootElement.classList.add("edit");

        this.rootElement.appendChild(
            jsml.div({ class: "gui edit-controls", contenteditable: false }, [
                jsml.button({
                    onClick: () => this.parentWidget.placeCommandBlock(this)
                }, jsml.span(_, "+")),
                jsml.div(
                    {
                        class: "button drag-handle" + (std_browser() === "firefox" ? " ff-fix" : ""),
                        draggable: "true",
                        onDragStart: evt => {
                            this.select();

                            beingDragged = this.rootElement;

                            evt.dataTransfer.setData("text/html", "widget");
                            evt.dataTransfer.setDragImage(this.rootElement, 32, 32);
                            evt.dataTransfer.effectAllowed = "all";

                            document.body.classList.add("dragging");
                        }
                    },
                    jsml.span({ draggable: "true" }, "::")
                ),
                jsml.button({
                    onClick: () => {
                        if (this.parentWidget?.isLastAndDefault(this)) {
                            return;
                        }

                        this.remove();
                    }
                }, jsml.span(_, "✕"))
            ])
        );
    }

    isSelectAble() {
        return true;
    }

    isSelectionPropagable() {
        return true;
    }

    select() {
        if (this.isSelectAble()) {
            this.rootElement.classList.add(WIDGET_SELECTION_CLASS);
            return;
        }

        if (!this.isSelectionPropagable()) return;
        if (this.parentWidget === null || this.parentWidget === undefined) return;

        this.parentWidget.select();
    }

    toggleSelect() {
        if (this.isSelectAble()) {
            this.rootElement.classList.toggle(WIDGET_SELECTION_CLASS);
            this.removeParentSelect();
            return;
        }

        if (!this.isSelectionPropagable()) return;
        if (this.parentWidget === null || this.parentWidget === undefined) return;

        this.parentWidget.toggleSelect();
    }

    removeSelect(isRecursive = true) {
        this.rootElement.classList.remove(WIDGET_SELECTION_CLASS);

        if (!isRecursive) return;

        for (const child of this.children) {
            child.removeSelect();
        }
    }

    removeParentSelect() {
        this.parentWidget?.removeSelect(false);
        this.parentWidget?.removeParentSelect();
    }

    /**
     * @param {Widget} after
     */
    placeCommandBlock(after) {
        if (document?.widgetElement.editable !== true) return;

        const indexOfAfter = this.children.indexOf(after);

        if (this.children[indexOfAfter + 1] instanceof WCommand) return;

        const cmd = WCommand.default(this, true);
        this.children.splice(indexOfAfter + 1, 0, cmd);

        if (indexOfAfter + 2 === this.children.length) {
            this.rootElement.appendChild(cmd.rootElement);
        } else {
            this.rootElement.insertBefore(cmd.rootElement, this.children[indexOfAfter + 2].rootElement);
        }

        cmd.rootElement.focus();
    }

    /**
     * @param {boolean} includeSelf
     * @return {Widget|null}
     */
    getClosestConfinedContainer(includeSelf = false) {
        let widget = this;
        do {
            if (widget.isConfinedContainer === true && includeSelf === true) {
                return widget;
            }

            widget = widget.parentWidget;
            includeSelf = true;
        } while (widget !== null && widget !== undefined);
        return null;
    }
}



//? WIDGET CONTAINER



class ContainerWidget extends Widget {
    /**
     * @typedef {"multiple" | number} ContainerWidgetChildSupport
     */

    #doRemoveFirstDefault = false;

    defaultChild;

    /**
     * @param {HTMLElement} root
     * @param {Widget} parent
     * @param {boolean} editable
     * @param {typeof Widget} defaultChild
     * @param {boolean} doRemoveFirstChild
     */
    constructor(root, parent, editable = false, defaultChild = undefined, doRemoveFirstChild = true) {
        super(root, parent, editable);

        if (editable) {
            this.defaultChild = defaultChild ?? WCommand;
        }

        if (editable && doRemoveFirstChild) {
            this.appendWidget(this.defaultChild.default(this, editable));
            this.#doRemoveFirstDefault = doRemoveFirstChild;
        }
    }

    /**
     * @param {Widget} after
     * @returns {Widget}
     */
    nextDefault(after) {
        if (document?.widgetElement.editable !== true) return null;

        const indexOfAfter = this.children.indexOf(after);
        const defaultWidget = this.defaultChild.default(this, this.editable);

        this.children.splice(indexOfAfter + 1, 0, defaultWidget);

        if (indexOfAfter + 2 === this.children.length) {
            this.rootElement.appendChild(defaultWidget.rootElement);
        } else {
            this.rootElement.insertBefore(defaultWidget.rootElement, this.children[indexOfAfter + 2].rootElement);
        }

        return defaultWidget;
    }

    createConfinedContainer() {
        this.rootElement.classList.add("confined-container");
        this.isConfinedContainer = true;
    }

    /**
     * @override
     * @param {ContainerWidgetChildSupport} value
     */
    set childSupport(value) {
        if (value === "none" || value <= 0) {
            console.error("ContainerWidget must accept children.");
            return;
        }

        super.childSupport = value;
    }

    /**
     * @override
     * @param {Widget} widget
     * @param {boolean} doRemoveFromRootElement
     * @param {boolean} doAnimate
     * @returns {Promise<boolean> | boolean}
     */
    removeWidget(widget, doRemoveFromRootElement = true, doAnimate = true) {
        const result = super.removeWidget(widget, doRemoveFromRootElement, doAnimate);

        // when child (Widget) length is 0 place new default (command block)
        if (this.children.length === 0) {
            this.appendWidget(this.defaultChild.default(this, this.editable));
        }

        return result;
    }

    isLastAndDefault(widget) {
        return widget.constructor.name === this.defaultChild.name && this.children.length === 1;
    }

    childRemoved(child, beforeRemovalIndex) {
        super.childRemoved(child, beforeRemovalIndex);

        if (this.children.length === 0) {
            this.appendWidget(this.defaultChild.default(this, this.editable));
        }
    }


    /**
     * @param {Widget} widget
     * @param {boolean} doAppendToRootElement
     */
    appendWidget(widget, doAppendToRootElement = true) {
        this.#adoptWidget(widget);
        return super.appendWidget(widget);
    }

    insertBeforeWidget(widget, child = null, doInsertToRootElement = true) {
        this.#adoptWidget(widget);
        return super.insertBeforeWidget(widget, child, doInsertToRootElement);
    }

    replaceWidget(find, replacement) {
        this.#adoptWidget(replacement);
        super.replaceWidget(find, replacement);
    }

    /**
     * @param {Widget} widget
     */
    #adoptWidget(widget) {
        if (this.#doRemoveFirstDefault === true) {
            super.removeWidget(this.children[0]);
            this.#doRemoveFirstDefault = false;
        }

        if (this.allowsDragAndDrop()) {
            widget.rootElement.ondragover = (evt) => {
                if (editor_getClosestByClass(widget.rootElement, "confined-container", false)?.constructor.name !== editor_getClosestByClass(beingDragged, "confined-container", false)?.constructor.name) return;
                this.updateDragHint(widget.rootElement, evt);
            };
            widget.rootElement.ondragleave = (evt) => this.removeDragHint(widget.rootElement, evt);
        }
    }

    dragHint;

    /**
     * @param {HTMLElement} currentTarget
     * @param {DragEvent} evt
     */
    updateDragHint(currentTarget, evt) {
        const widgetRect = currentTarget.getBoundingClientRect();
        const percentage = (evt.clientY - widgetRect.top) / widgetRect.height;
        let dragHint = $(".drag-hint");

        if (percentage <= 0.5) {
            if (currentTarget.previousElementSibling !== null && currentTarget.previousElementSibling.classList.contains("drag-hint")) {
                return;
            }

            if (dragHint === null) {
                dragHint = Div("drag-hint", __, { attributes: { contenteditable: true } });
                currentTarget.parentElement.insertBefore(dragHint, currentTarget);
                dragHint.classList.add("expand");
                return;
            }

            dragHint.classList.remove("expand");
            setTimeout(() => {
                currentTarget.parentElement.insertBefore(dragHint, currentTarget);
                dragHint.classList.add("expand");
            }, 100);
            return;
        }

        if (currentTarget.nextElementSibling !== null && currentTarget.nextElementSibling.classList.contains("drag-hint")) {
            return;
        }

        if (dragHint === null) {
            dragHint = jsml.div({ class: "drag-hint", contenteditable: true });
            if (currentTarget.nextElementSibling === null) {
                currentTarget.parentElement.appendChild(dragHint);
            } else {
                currentTarget.parentElement.insertBefore(dragHint, currentTarget.nextElementSibling);
            }
            dragHint.classList.add("expand");
            return;
        }

        dragHint.classList.remove("expand");
        setTimeout(() => {
            if (currentTarget.nextElementSibling === null) {
                currentTarget.parentElement.appendChild(dragHint);
            } else {
                currentTarget.parentElement.insertBefore(dragHint, currentTarget.nextElementSibling);
            }
            dragHint.classList.add("expand");
        }, 100);
    }

    /**
     * @param {HTMLElement} currentTarget
     * @param {DragEvent} evt
     */
    removeDragHint(currentTarget, evt) {
        // $(".drag-hint")?.remove();
    }

    allowsDragAndDrop() {
        return true;
    }
}



class WidgetRegistry {
    /**
     * @type {Object.<string, typeof Widget>}
     */
    #map = {};
    /**
     * @callback WidgetRegistryCallback
     * @param {typeof Widget} clazz
     */
    /**
     * @type {Object.<string, WidgetRegistryCallback[] | undefined>}
     */
    #callbacks = {};

    #setOff(className) {
        if (this.#callbacks[className] === undefined) return;

        for (const callback of this.#callbacks[className]) {
            callback(this.#map[className]);
        }
    }

    /**
     * @param {string} className
     * @param {typeof Widget} constructor
     */
    define(className, constructor) {
        this.#map[className] = constructor;
        this.#setOff(className);
    }


    /**
     * @param {string} className
     */
    get(className) {
        return this.#map[className];
    }


    /**
     * @param {string} className
     */
    exists(className) {
        return this.#map[className] !== undefined;
    }


    /**
     * @param {...string} classNames
     * @return {Promise<Awaited<void>[]>}
     */
    // request(...classNames) {
    //     let requestClassNames = "";
    //     const widgetPromises = [];
    //
    //     for (const className of classNames) {
    //         if (this.exists(className)) continue;
    //
    //         requestClassNames += className + ",";
    //         widgetPromises.push(
    //             new Promise(resolve => this.on(className, resolve))
    //         );
    //     }
    //
    //     if (requestClassNames === "") {
    //         return Promise.resolve([]);
    //     }
    //
    //     const subtrahend = Object.keys(this.#map);
    //     const query = "?widgets=" + requestClassNames.substring(0, requestClassNames.length - 1) + (subtrahend.length !== 0
    //         ? `&subtrahend=${subtrahend.join(",")}`
    //         : "");
    //
    //     AJAX.get("/bundler/css/" + query, TextHandler(), AJAX.CORS_OPTIONS, AJAX.SERVER_HOME)
    //         .then(text => {
    //             document.head.appendChild(
    //                 Component("style", __, HTML(text))
    //             );
    //         });
    //     AJAX.get("/bundler/js/" + query, TextHandler(), AJAX.CORS_OPTIONS, AJAX.SERVER_HOME)
    //         .then(text => {
    //             document.head.appendChild(
    //                 Component("script", __, HTML(text))
    //             );
    //         });
    //
    //     return Promise.all(widgetPromises);
    // }



    /**
     * @param {string} className
     * @param {WidgetRegistryCallback} callback
     */
    on(className, callback) {
        if (this.#map[className] !== undefined) {
            callback(this.#map[className]);
            return;
        }

        if (this.#callbacks[className] === undefined) {
            this.#callbacks[className] = [];
        }

        this.#callbacks[className].push(callback);
    }
}



const widgets = new WidgetRegistry();



/**
 * @typedef Page
 * @property {Widget} root
 * @property {HTMLElement} inspector
 */
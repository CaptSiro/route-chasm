class WFileDownload extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef DownloadFile
     * @property {string} name
     * @property {string} src
     */
    /**
     * @typedef FileDownloadJSONType
     * @property {DownloadFile[]=} files
     * @property {string=} name
     *
     * @typedef {FileDownloadJSONType & WidgetJSON} FileDownloadJSON
     */

    static DOWNLOAD_ICON = "w-file-download-icon";
    /**
     * @type {Observable<DownloadFile[]>}
     */
    #files;
    /**
     * @type {Observable<number>}
     */
    #downloadSize;
    #fileView;
    #name;


    /**
     * @param {FileDownloadJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        const buttonLike = (
            jsml.div("container", [
                Icon("nf-oct-download"),
                jsml.span(_, "Download")
            ])
        );

        super(jsml.div("w-file-download center", buttonLike), parent, editable);
        this.childSupport = this.childSupport;

        this.#name = json.name;
        this.#files = new Observable([]);
        this.#downloadSize = new Observable(0);
        this.#files.onChange(async files => {
            const fileSources = files
                .map(file => file.src)
                .join(",");

            // this.#downloadSize.value = Number(
            //     await AJAX.get(
            //         `/file/size/?files=${fileSources}&website=${webpage.website}`,
            //         TextHandler(),
            //         { headers: { "Access-Control-Allow-Origin": "*" } },
            //         AJAX.SERVER_HOME
            //     )
            // );
        });

        if (json.files !== undefined) {
            this.#files.value = json.files;
        }

        if (editable) {
            this.appendEditGui();
        }

        buttonLike.addEventListener("click", evt => {
            document.body?.classList.remove("cursor-pointer");
            const canBeTriggered = evt.ctrlKey || editable === false;

            if (canBeTriggered) {
                evt.preventDefault();
                evt.stopImmediatePropagation();

                const isConfirmed = confirm(
                    `Do you want to download ${
                        this.#files.value.length > 1
                            ? "these files? They"
                            : "this file? It"
                    } may be a virus hazard.\nSize: ${WFileDownload.sizeFormatter(this.#downloadSize.value)}`
                );

                if (isConfirmed) {
                    // const url = new URL(`${AJAX.SERVER_HOME}/file/download`);
                    // url.searchParams.set("website", webpage.website);
                    // url.searchParams.set("files", this.#files.value
                    //     .map(file => file.src)
                    //     .join(","));
                    // url.searchParams.set("name", this.#name ?? "");
                    //
                    // window.location.replace(url);
                }
            }
        });
    }

    static sizeFormatter(size, inPowerOfTwo = false, decimal = 1) {
        const divider = inPowerOfTwo ? 1000 : 1024;

        if (Math.abs(size) < divider) {
            return size + " B";
        }

        const units = divider
            ? ["kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"]
            : ["KiB", "MiB", "GiB", "TiB", "PiB", "EiB", "ZiB", "YiB"];

        let unit = -1;
        const decimalPlaces = 10 ** decimal;

        do {
            size /= divider;
            unit++;
        } while (Math.round(Math.abs(size) * decimalPlaces) / decimalPlaces >= divider && unit < units.length - 1);

        return size.toFixed(decimal) + " " + units[unit];
    }

    /**
     * @override
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WFileDownload}
     */
    static default(parent, editable = false) {
        return new WFileDownload({}, parent, editable);
    }

    /**
     * @override
     * @param {FileDownloadJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     * @returns {WFileDownload}
     */
    static build(json, parent, editable = false) {
        return new WFileDownload(json, parent, editable);
    }

    /**
     * @override
     * @returns {Content}
     */
    get inspectorHTML() {
        if (this.#fileView === undefined) {
            this.#fileView = jsml.ul("files-inspector");

            const appendFiles = files => {
                this.#fileView.textContent = undefined;


                for (const file of files) {
                    this.#fileView.appendChild(
                        //     new TextSlider(jsml.div("i-row", file.name), { gap: 50, speed: 75 }).element
                        jsml.li("i-row", file.name)
                    );
                }
            };

            this.#files.onChange(appendFiles);
            appendFiles(this.#files.value);
        }

        return [
            TitleInspector("File download"),

            HRInspector(),

            TitleInspector("Properties"),
            TextFieldInspector(this.#name, value => {
                this.#name = value;
                return true;
            }, "Download as", "best-wallpaper"),
            jsml.div("i-row", [
                jsml.span(_, "Files"),
                jsml.button({
                    class: "button",
                    onClick: evt => {
                        // const win = showWindow("file-select");
                        // win.dataset.multiple = "true";
                        // win.dataset.fileType = "";
                        // win.dispatchEvent(new Event("fetch"));
                        // win.onsubmit = submitEvent => {
                        //     this.#files.value = submitEvent.detail.map(file => ({ name: file.name, src: file.src }));
                        //     std_dom_validated(evt.target.parentElement);
                        // };
                    }
                }, "Select")
            ]),
            this.#fileView
        ];
    }

    /**
     * @override
     * @returns {WidgetJSON}
     */
    save() {
        return {
            type: "WFileDownload",
            files: this.#files.value,
            name: this.#name
        };
    }

    focus() {
        editor_inspect(this.inspectorHTML, this);
    }
}



widgets.define("WFileDownload", WFileDownload);
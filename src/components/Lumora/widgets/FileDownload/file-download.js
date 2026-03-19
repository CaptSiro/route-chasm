class WFileDownload extends Widget {

    // use json.child for single child widget like Center
    // or json.children for array of widgets
    /**
     * @typedef FileDownloadJSONType
     * @property {string=} hash
     * @property {string=} url
     * @property {string=} fileName
     * @property {string=} downloadName
     * @property {string=} size
     *
     * @typedef {FileDownloadJSONType & WidgetJSON} FileDownloadJSON
     */

    static DOWNLOAD_ICON = "w-file-download-icon";
    /** @type {Observable<string>} */
    #hash;
    /** @type {Observable<string>} */
    #size;
    #fileView;
    /** @type {Observable<string>} */
    #fileName;
    /** @type {Observable<string | null>} */
    #downloadName;
    #url;


    /**
     * @param {FileDownloadJSON} json
     * @param {Widget} parent
     * @param {boolean} editable
     */
    constructor(json, parent, editable = false) {
        const buttonLabel = jsml.span(_, "Download");
        const button = (
            jsml.button({
                class: "container",
                disabled: !is(json.url)
            }, [
                Icon("nf-oct-download"),
                buttonLabel
            ])
        );

        super(jsml.div("w-file-download center", button), parent, editable);
        this.childSupport = this.childSupport;

        const updateUrl = () => {
            const api = api_loadFileSystem();
            const url = api.createDownloadUrl(this.#hash.value, this.getFileName());
            button.disabled = !is(this.#url);
            if (!is(url)) {
                return;
            }

            this.#url = url;
        }

        this.#downloadName = new Observable(json.downloadName ?? null);
        this.#downloadName.onChange(() => {
            updateUrl();
            buttonLabel.textContent = this.getFileName();
        });

        this.#url = json.url;

        this.#fileName = new Observable(json.fileName ?? '');
        this.#size = new Observable(json.size ?? '0 B');

        this.#hash = new Observable(json.hash ?? '');
        this.#hash.onChange(async hash => {
            updateUrl();

            if (hash.length === 0) {
                return;
            }

            const api = api_loadFileSystem();
            const url = api.createInfoUrl(hash);
            if (!is(url)) {
                return;
            }

            const response = await fetch(std_jsonEndpoint(url));
            if (await std_fetch_handleServerError(response)) {
                return;
            }

            if (!response.ok) {
                throw new Error('Could not fetch file size');
            }

            /** @type {FileInfo} */
            const json = await response.json();
            if (!is(json['sizeHumanReadable'])) {
                return;
            }

            this.#size.value = json['sizeHumanReadable'];
            this.#fileName.value = json['fileName'];
        });

        buttonLabel.textContent = this.getFileName();

        if (editable) {
            this.appendEditGui();
        }

        button.addEventListener("click", async evt => {
            document.body?.classList.remove("cursor-pointer");
            const canBeTriggered = evt.ctrlKey || editable === false;

            if (!is(this.#url)) {
                await window_alert('No file has been assigned');
                return;
            }

            if (!canBeTriggered) {
                return;
            }

            evt.preventDefault();
            evt.stopImmediatePropagation();

            const isConfirmed = await window_confirm(
                `Do you want to download ${this.getFileName()}? It may be a virus hazard.\nSize: ${this.#size.value}`
            );

            if (!isConfirmed) {
                return;
            }

            location.replace(this.#url);
        });
    }

    getFileName() {
        const download = this.#downloadName.value;
        if (!is(download) || download.length === 0) {
            return this.#fileName.value;
        }

        return download;
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
            const fileName = jsml.span(_, this.#fileName.value);
            const size = jsml.span(_, this.#size.value);

            this.#fileView = jsml.div("files-inspector", [
                jsml.div('i-row', ['Selected: ', fileName]),
                jsml.div('i-row', ['Size: ', size]),
            ]);

            this.#fileName.onChange(n => {
                fileName.textContent = n;
            });

            this.#size.onChange(s => {
                size.textContent = s;
            });
        }

        return [
            TitleInspector("File download"),

            HRInspector(),

            TitleInspector("Properties"),
            TextFieldInspector(this.#downloadName.value, (value, parentElement) => {
                this.#downloadName.value = value;
                std_dom_validated(parentElement);
                return true;
            }, "Download as", "best-wallpaper"),

            jsml.div("i-row", [
                jsml.span(_, "File"),
                jsml.button({
                    class: "button",
                    onClick: async evt => {
                        const api = api_loadFileSystem();
                        const hash = await window_fileSelect(api.createDirectoryUrl());
                        if (!is(hash)) {
                            return;
                        }

                        this.#hash.value = hash;
                    }
                }, "Select")
            ]),
            this.#fileView
        ];
    }

    /**
     * @override
     * @returns {FileDownloadJSON}
     */
    save() {
        return {
            type: "WFileDownload",
            fileName: this.#fileName.value,
            downloadName: this.#downloadName.value,
            url: this.#url,
            size: this.#size.value,
            hash: this.#hash.value,
        };
    }

    focus() {
        editor_inspect(this.inspectorHTML, this);
    }
}



widgets.define("WFileDownload", WFileDownload);
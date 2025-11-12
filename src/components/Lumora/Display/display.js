const display_viewport = $("#viewport");

lumora_viewport_set(display_viewport, display_viewport_resize);

const viewportSizes = [{
    maxPixels: 1200,
    className: "viewport-tablet"
}, {
    maxPixels: 600,
    className: "viewport-smartphone"
}, {
    maxPixels: 425,
    className: "viewport-small-smartphone"
}];
new ResizeObserver(entries => {
    const viewport = entries[0];

    for (const size of viewportSizes) {
        if (size.maxPixels >= viewport.contentRect.width) {
            viewport.target.classList.add(size.className);
            continue;
        }

        viewport.target.classList.remove(size.className);
    }
}).observe(display_viewport);




window.addEventListener("resize", lumora_viewport_resize);

function display_viewport_resize() {
    const width = window.innerWidth;
    const height = window.innerHeight;

    lumora_viewport_dimension = {
        width,
        height,
        convertedHeight: height,
        convertedWidth: width,
        maxHeight: height,
        maxWidth: width,
        targetWidth: width,
        targetHeight: height,
        targetAspectRatio: width / height
    }
}

std_onLoad(() => {
    window.addEventListener("load", async () => {
        const displayData = $("#display-data");
        const root = await WRoot.buildAsync(displayData.textContent);
        displayData.remove();

        window.rootWidget = root;
        const viewport = $("#viewport");
        viewport.appendChild(root.rootElement);
        document.widgetElement = root;
    }, { once: true });
});

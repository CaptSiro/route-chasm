/**
 * @param {HTMLElement} element
 * @param {string} widthSelector
 * @param {string} heightSelector
 */
function imageVariantPreview_preview(element, { widthSelector, heightSelector }) {
    const width = $(widthSelector);
    const height = $(heightSelector);
    const size = $('.size', element);

    if (!is(width) || !is(height) || !is(size)) {
        return;
    }

    let w = Number(width.value);
    let h = Number(height.value);

    const update = () => {
        let s = '';
        if (w <= 0) {
            element.style.width = "300px";
            element.style.height = "300px";
            element.style.aspectRatio = "unset";
            s += '∞';
        } else {
            s += w;
        }

        s += 'x';

        if (h <= 0) {
            element.style.width = "300px";
            element.style.height = "300px";
            element.style.aspectRatio = "unset";
            s += '∞';
        } else {
            s += h;
        }

        size.textContent = s;

        if (w > 0 && h > 0) {
            if (w > h) {
                element.style.width = "300px";
                element.style.height = "unset";
            }

            if (h > w) {
                element.style.height = "300px";
                element.style.width = "unset";
            }

            element.style.aspectRatio = w + " / " + h;
        }
    }

    width.addEventListener("input", () => {
        w = Number(width.value);
        update();
    });

    height.addEventListener("input", () => {
        h = Number(height.value);
        update();
    });

    update();
}
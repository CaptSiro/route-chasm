const SPTF_KEY_HIDE_PASSED_TESTS = "sptf_hpt";
let sptf_isInitialized = false;

/**
 * @param {boolean|undefined} force
 */
function sptf_togglePassedSuites(force = undefined) {
    const suites = $$(".suite.pass");

    for (const suite of suites) {
        suite.classList.toggle("hide", force);
    }

    if (suites.length > 0) {
        localStorage.setItem(SPTF_KEY_HIDE_PASSED_TESTS, String(suites[0].classList.contains("hide")));
    }
}

function sptf_init() {
    if (sptf_isInitialized) {
        return;
    }

    sptf_isInitialized = true;
    const hide = JSON.parse(localStorage.getItem(SPTF_KEY_HIDE_PASSED_TESTS) ?? "false");
    if (hide) {
        sptf_togglePassedSuites(true);
    }

    const sptf = $(".sptf");
    for (const testFile of Array.from($$(".test-file", sptf)).reverse()) {
        if (testFile instanceof HTMLElement) {
            if (testFile.dataset.failed > 0) {
                sptf.prepend(testFile);
            }

            for (const suite of Array.from($$(".suite.fail", testFile)).reverse()) {
                suite.parentElement.prepend(suite);
            }
        }
    }
}

/**
 * @param {HTMLElement} button
 */
function sptf_hideButton(button) {
    sptf_init();
    button.addEventListener("click", () => sptf_togglePassedSuites());
}
const FILE_STATE = "fs";
const files = document.querySelectorAll(".file");
const snapshot = localStorage.getItem(FILE_STATE) ?? "";

for (let i = 0; i < files.length; i++) {
    const file = files[i];

    file.addEventListener('click', () => {
        const b = file.nextElementSibling.style.display === "none";
        file.nextElementSibling.style.display = b
            ? "unset"
            : "none";

        let state = localStorage.getItem(FILE_STATE) ?? "";

        if (state.length <= i) {
            state += "1".repeat(i - state.length);
            state += String(Number(b));
        } else {
            state = state.substring(0, i) + String(Number(b)) + state.substring(i + 1);
        }

        localStorage.setItem(FILE_STATE, state);
    });

    if (snapshot[i] === undefined) {
        continue;
    }

    file.nextElementSibling.style.display = Boolean(Number(snapshot[i]))
        ? "unset"
        : "none"
}
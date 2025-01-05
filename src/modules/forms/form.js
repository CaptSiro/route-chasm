/**
 * @param {HTMLElement} form
 */
async function submitForm(form) {
    const data = new FormData();
    const json = {};
    for (const input of form.querySelectorAll("[name]")) {
        data[input.name] = input.value;
        json[input.name] = input.value;
    }

    const response = await fetch(window.location, {
        method: form.dataset.method,
        body: data
    });

    console.log(await response.text());


    const jsonResponse = await fetch(window.location, {
        method: form.dataset.method,
        body: JSON.stringify(json)
    });

    console.log(await jsonResponse.text());
}
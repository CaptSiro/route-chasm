/**
 * @param {boolean} isOnline
 * @private
 */
function _internetStatus(isOnline) {
    document.body.classList.toggle('online', isOnline);
    document.body.classList.toggle('offline', !isOnline);
}

window.addEventListener('load', () => {
    window.addEventListener('online', () => _internetStatus(true));
    window.addEventListener('offline', () => _internetStatus(false));

    _internetStatus(window.navigator.onLine);
});



/**
 * @param {number} a1
 * @param {number} b1
 * @param {number} a2
 * @param {number} b2
 */
function std_rangeInRange(a1, b1, a2, b2) {
    if (b1 > a1) {
        const t = a1;
        a1 = b1;
        b1 = t;
    }

    if (b2 > a2) {
        const t = a2;
        a2 = b2;
        b2 = t;
    }

    return (a1 <= a2 && b2 <= b1)
        || (a2 <= a1 && b1 <= b2);
}



/**
 * @param {HTMLElement} child
 * @param {HTMLElement} parent
 */
function std_scrollIntoView(child, parent) {
    const childRect = child.getBoundingClientRect();
    const parentRect = parent.getBoundingClientRect();

    const scroll = parent.scrollTop;

    const parentA = scroll;
    const parentB = parentA + parentRect.height;

    const childA = child.offsetTop - parentRect.top;
    const childB = childA + childRect.height;

    if (std_rangeInRange(parentA, parentB, childA, childB)) {
        return;
    }

    let top = scroll;

    // childA        parentA  childB            parentB
    //    > ########### >       <                  <
    if (childA < parentA) {
        top -= Math.abs(parentA - childA);

        // parentA        childA  parentB            childB
        //    >             >       < ################ <
    } else if (parentB < childB) {
        top += Math.abs(childB - parentB);
    }

    parent.scrollTo({
        top,
        behavior: "smooth"
    });
}

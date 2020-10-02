"use strict";

document.addEventListener("submit", e => {
    const pages = document.querySelectorAll(".survey-page");
    const current = document.querySelector(".current-page");
    const index = Array.from(pages).indexOf(current);
    current.classList.remove("current-page");
    
    if (index < pages.length - 1) {
        pages[index + 1].classList.add("current-page");
        pages[index + 1].querySelectorAll(".awaiting-page").forEach(
            required => required.disabled = false
        );
        e.preventDefault();
        e.stopPropagation();
    }
}, true);

document.querySelectorAll(".survey-page:not(.current-page) :invalid").forEach(
    required => {
        required.disabled = true;
        required.classList.add("awaiting-page");
    }
);

COLLECTOR.autofocus = false;

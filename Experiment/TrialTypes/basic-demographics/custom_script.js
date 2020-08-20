"use strict";

var get_input = function(name) {
    return document.querySelector(`input[name='${name}']`);
};

var get_birthday_bounds = function(age) {
    let date = new Date();
    date.setFullYear(date.getFullYear() - age - 1);
    let min = date.valueOf();
    date.setFullYear(date.getFullYear() + 1);
    let max = date.valueOf();
    return [min, max];
}

var check_age_and_birthday = function() {
    let age = get_input("Age").value - 0;
    let birthday = get_input("Birthday").value;
    let birthday_timestamp = Date.parse(birthday);
    
    if (Number.isNaN(birthday_timestamp) || Number.isNaN(age)) return;
    
    let birthday_bounds = get_birthday_bounds(age);
    let birthday_within_bounds = (birthday_timestamp >= birthday_bounds[0]
        && birthday_timestamp <= birthday_bounds[1]);
    let warning = document.getElementById("age-warning");
    let button = document.getElementById("FormSubmitButton");
    
    if (birthday_within_bounds) {
        warning.classList.add("invis");
        button.disabled = false;
    } else {
        warning.classList.remove("invis");
        button.disabled = true;
    }
};

document.addEventListener("input", check_age_and_birthday);

"use strict";

const trial_time = 2;
const trial_iti = 0.25;

function initialize_trial() {
    window.current_trial = 0;
    start_next_trial();
    start_capturing_keypresses();
}

// main loop for progressing through trials
function start_next_trial() {
    const trials = document.querySelectorAll(".trial");
    const trial = trials[window.current_trial];
    trial.classList.add("current-trial");
    set_focus(trial);
    setTimeout(end_current_trial, trial_time * 1000);
}

function set_focus(container) {
    $(container).find(":focusable:first").focusWithoutScrolling();
}

function end_current_trial() {
    document.querySelector(".current-trial").classList.remove("current-trial");
    ++window.current_trial;
    const trial_count = get_trial_count();
    
    if (window.current_trial < trial_count) {
        advance_trial();
    } else {
        submit_trial();
    }
}

function advance_trial() {
    if (trial_iti > 0) {
        setTimeout(start_next_trial, trial_iti * 1000);
    } else {
        start_next_trial();
    }
}

function submit_trial() {
    $("#FormSubmitButton").click();
}

// recording keypresses
function start_capturing_keypresses() {
    initialize_trial_spacebars();
    
    document.addEventListener("keydown", e => {
        if (e.key === " ") {
            e.preventDefault();
            capture_spacebar();
        }
    });
}

function initialize_trial_spacebars() {
    const trial_count = get_trial_count();
    window.trials_with_spacebar = Array(trial_count).fill(0);
    window.spacebar_timestamps = [];
    set_spacebar_input_values();
}

function capture_spacebar() {
    window.spacebar_timestamps.push(COLLECTOR.getRT());
    window.trials_with_spacebar[window.current_trial] = 1;
    set_spacebar_input_values();
}

function set_spacebar_input_values() {
    get_input("Spacebar_Timestamps").value = window.spacebar_timestamps.join(",");
    get_input("Trials_With_Response").value = window.trials_with_spacebar.join(",");
}

// utility functions
function get_input(name) {
    return document.querySelector(`input[name='${name}']`);
}

function get_trial_count() {
    return document.querySelectorAll(".trial").length;
}

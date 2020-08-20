"use strict";

function set_checked_for_group_boxes() {
    document.querySelectorAll(".col-group-checkbox").forEach(check_group_checkbox);
    
    document.querySelectorAll(".user-group-checkbox").forEach(box => {
        update_user_group_checkbox(box.parentElement.parentElement);
    });
}

function check_group_checkbox(group_box) {
    const container = group_box.parentElement.parentElement.parentElement;
    const checkboxes = container.querySelectorAll("input:not(.col-group-checkbox)");
    let checked_count = 0;
    
    for (let i = 0; i < checkboxes.length; ++i) {
        if (checkboxes[i].checked) ++checked_count;
    }
    
    if (checked_count === checkboxes.length) {
        group_box.indeterminate = false;
        group_box.checked = true;
    } else if (checked_count === 0) {
        group_box.indeterminate = false;
        group_box.checked = false;
    } else {
        group_box.indeterminate = true;
        group_box.checked = false;
    }
}

function set_checked_for_group_box_targets(group_box) {
    const container = group_box.parentElement.parentElement.parentElement;
    const checkboxes = container.querySelectorAll("input[type='checkbox']");
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = group_box.checked;
        checkbox.indeterminate = false;
    });
}

function set_checked_for_group_boxes_of_target(checkbox) {
    let parent = checkbox.parentElement;
    
    while (parent !== null) {
        let group_box = get_container_group_box(parent);
        
        if (group_box) check_group_checkbox(group_box);
        
        parent = parent.parentElement;
    }
}

function distribute_user_checkbox_status(checkbox) {
    let row = checkbox.parentElement.parentElement;
    let level = ("level" in row.dataset) ? (parseInt(row.dataset.level) + 1) : 1;
    
    if (level > 1) {
        set_checked_for_targets(row);
    }
    
    row = row.previousElementSibling;
    
    while (row) {
        if ("level" in row.dataset && row.dataset.level >= level) {
            update_user_group_checkbox(row);
            ++level;
        }
        
        row = row.previousElementSibling;
    }
}

function set_checked_for_targets(row) {
    let targets = get_user_group_targets(row);
    const status = row.querySelector("input").checked;
    
    targets.forEach(target => {
        target.checked = status;
        target.indeterminate = false;
    });
}

function update_user_group_checkbox(row) {
    let box = row.querySelector("input");
    let targets = get_user_group_targets(row);
    let checked_count = 0;
    
    targets.forEach(target => {
        if (target.checked) ++checked_count;
    });
    
    if (checked_count === targets.length) {
        box.checked = true;
        box.indeterminate = false;
    } else if (checked_count === 0) {
        box.checked = false;
        box.indeterminate = false;
    } else {
        box.checked = false;
        box.indeterminate = true;
    }
}

function get_user_group_targets(row) {
    let level = row.dataset.level;
    const targets = [];
    row = row.nextElementSibling;
    
    while (row) {
        if ("level" in row.dataset && row.dataset.level >= level) {
            break;
        }
        
        targets.push(row.querySelector("input"));
        row = row.nextElementSibling;
    }
    
    return targets;
}

function get_container_group_box(container) {
    return container.querySelector(":scope > label > span > input.col-group-checkbox");
}

document.addEventListener("DOMContentLoaded", e => {
    set_checked_for_group_boxes();
    
    // col checkboxes
    document.querySelectorAll(".col-group-checkbox").forEach(group_box => {
        group_box.addEventListener("input", e => {
            set_checked_for_group_box_targets(group_box);
            set_checked_for_group_boxes_of_target(group_box);
        });
    });
    
    document.getElementById("data-columns").addEventListener("input", e => {
        if (e.target.tagName === "INPUT"
            && e.target.type === "checkbox"
            && !e.target.classList.contains("col-group-checkbox")
        ) {
            set_checked_for_group_boxes_of_target(e.target);
        }
    });
    
    // user checkboxes
    document.getElementById("users").addEventListener("input", e => {
        if (e.target.tagName === "INPUT"
            && e.target.type === "checkbox"
        ) {
            distribute_user_checkbox_status(e.target);
        }
    });
});

<script>

var trial_start_timestamp = null;
var alien_catch_over = false;
var settings = <?= json_encode($settings_parsed) ?>;

var get_random_num = function(min, max) {
    let num_min = Number(min);
    let num_max = Number(max);
    
    return Math.random() * (num_max - num_min) + num_min;
};

var get_input_by_name = function(input_name) {
    return document.querySelector(`input[name='${input_name}']`);
};

var get_alien_side = function() {
    if (typeof settings.force_alien_side !== "undefined") {
        return settings.force_alien_side;
    } else {
        return Math.random() < 0.5 ? "left" : "right";
    }
};

var alien_keydown = function(e) {
    if (e.key !== settings.left_key && e.key !== settings.right_key) return;
    
    let alien_rt = get_input_by_name("alien_rt");
    
    if (alien_rt.value !== "no response") return;
    
    alien_rt.value = Date.now() - window.trial_start_timestamp;
    
    let response = e.key === settings.left_key
                 ? "left"
                 : "right";
    
    get_input_by_name("alien_response").value = response;
    
    get_input_by_name("alien_response_correct").value = 
        response === get_input_by_name("alien_side").value
        ? "1"
        : "0";
    
    // end_alien_catch();
};

var end_alien_catch = function() {
    if (alien_catch_over) return;
    
    alien_catch_over = true;
    hide_alien();
    COLLECTOR.timer(0.5, show_catch_result);
};

var hide_alien = function() {
    var container = document.querySelector(".canvas_container");
    container.classList.remove("side_left");
    container.classList.remove("side_right");
};

var show_catch_result = function() {
    if (get_input_by_name("alien_response_correct").value !== "1") {
        give_failure_feedback();
    } else {
        show_centered_alien();
    }
};

var give_failure_feedback = function() {
    document.querySelector(".context_container").classList.add("hidden");
    
    if (prev_misses === 4) {
        end_experiment();
    } else {
        if (prev_misses === 2) {
            give_warning();
        }
        
        document.querySelector(".feedback").style.display = "block";
        COLLECTOR.timer(settings.feedback_time, submit_trial);
    }
};

var give_warning = function() {
    document.querySelector(".warning").style.visibility = "visible";
};

var end_experiment = function() {
    submit_trial();
};

var show_centered_alien = function() {
    var container = document.querySelector(".canvas_container");
    container.classList.add("centered");
    COLLECTOR.timer(settings.alien_final_study_time, submit_trial);
};

var submit_trial = function() {
    document.querySelector(".warning").style.visibility = "hidden";
    $("#FormSubmitButton").click();
};

var show_alien = function() {
    let alien_side = get_alien_side();
    get_input_by_name('alien_side').value = alien_side;
    document.querySelector(".canvas_container").classList.add(`side_${alien_side}`);
    
    document.addEventListener("keydown", alien_keydown);
    COLLECTOR.timer(settings.alien_appear_time, hide_alien);
    COLLECTOR.timer(settings.alien_catch_time, end_alien_catch);
};

COLLECTOR.experiment.<?= $trialType ?> = function() {
    let alien_delay = get_random_num(settings.alien_min_delay, settings.alien_max_delay);
    get_input_by_name('alien_delay').value = alien_delay;
    
    COLLECTOR.timer(alien_delay, show_alien);
    window.trial_start_timestamp = Date.now();
};

</script>

<style>
    body { font-size: 150%; line-height: 1.5em; }
    #clock {
        padding: 8px;
        background-color: #ddd;
        border: 2px solid #aaa;
        font-size: 125%;
        display: inline-block;
        margin-top: 30px;
    }
</style>

<div>
    <p><?= $text ?></p>
    <div class="textcenter"><span id="clock"></span></div>
</div>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<script>
"use strict";

function get_numeric_html_contents(selector) {
    let element = document.querySelector(selector);
    
    if (element === null) return NaN;
    
    const contents = element.textContent.trim();
    const numeric_contents = parseFloat(contents);
    
    return numeric_contents;
}

function enable_advance_button() {
    document.getElementById("advance-btn").disabled = false;
}

function get_formatted_time(seconds) {
    seconds = Math.round(seconds);
    const hours = Math.floor(seconds / 3600);
    seconds %= 3600;
    const minutes = Math.floor(seconds / 60);
    seconds %= 60;
    
    return (hours > 0 ? hours + ":" : "")
         + minutes.toString().padStart(2, "0") + ":"
         + seconds.toString().padStart(2, "0");
}

function start_clock(seconds) {
    const start = Date.now();
    const clock = document.getElementById("clock");
    
    const set_clock = function() {
        const time_elapsed = (Date.now() - start) / 1000;
        const time_left = Math.min(seconds, Math.max(0, seconds - time_elapsed));
        const time_formatted = get_formatted_time(time_left);
        clock.textContent = time_formatted;
    };
    
    setInterval(set_clock, 0.1);
    set_clock();
}

COLLECTOR.experiment["<?= $trialType ?>"] = function() {
    const min_time = get_numeric_html_contents("#minTime");
    const max_time = get_numeric_html_contents("#maxTime");
    
    if (!Number.isNaN(max_time) && max_time > 0) {
        start_clock(max_time);
    }
};

</script>

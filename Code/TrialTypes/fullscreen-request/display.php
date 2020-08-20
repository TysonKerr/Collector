<style>
    .instructions { font-size: 150%; }
    .invis { visibility: hidden; }
</style>

<div class="instructions">
<p>Thank you for your participation. Please:</p>

<ul>
    <li>Follow the instructions for each task and try your best to perform well</li>
    <li>Maximize your browser and focus completely on the task without any distractions</li>
    <li>DO NOT take notes during the experiment, because this interferes with our
        ability to accurately measure the learning process</li>
    <li>DO NOT participate if you feel you cannot fully commit to these requirements</li>
</ul>

<p>When you have maximized your browser and are ready to begin, please click
    "Next" to continue to the task instructions.</p>

<p>The "Next" button will appear once you are in full screen mode.</p>
</div>

<div class="textcenter invis" id="submit-container">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<script>
"use strict";

function has_fullscreened() {
    return (screen.width - window.innerWidth) <= 50;
}

function validate_fullscreen() {
    let el = document.getElementById("submit-container");
    el.classList.toggle("invis", !has_fullscreened());
}

document.addEventListener("DOMContentLoaded", validate_fullscreen);
window.addEventListener("resize", validate_fullscreen);
</script>

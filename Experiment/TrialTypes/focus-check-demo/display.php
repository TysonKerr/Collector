<style>
.instructions { font-size: 150%; }

#focus-warning {
    display: none;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100%;
    width: 100%;
    background-color: blue;
    font-size: 200%;
    color: white;
}

#focus-warning.shown {
    display: block;
}

#focus-warning > div {
    display: table;
    height: 100%;
    width: 100%;
}

#focus-warning > div > div {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
}
</style>

<div class="instructions"><?php echo $text; ?></div>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<div id="focus-warning"><div><div>
    <p>This window has lost focus.</p>
    <p>Please click here to re-enable keypresses.</p>
</div></div></div>

<script>
"use strict";

document.addEventListener("DOMContentLoaded", function() {
    let focused = true;
    let warning = document.getElementById("focus-warning").classList;
    
    let check_focus = function() {
        let current_focus = document.hasFocus();
        
        if (current_focus !== focused) {
            focused = current_focus;
            warning.toggle("shown", !focused);
        }
    }
    
    check_focus();
    
    window.addEventListener("focus", check_focus);
    window.addEventListener("blur",  check_focus);
});
</script>

<div class="cue"><?= $answer ?></div>

<div class="response-area">
    <button type="button" value="old">Old</button>
    <button type="button" value="new">New</button>
</div>

<div class="hidden">
    <input type="hidden" name="Response" value="no response">
    <button type="submit" id="FormSubmitButton">Submit</button>
</div>

<script>
"use strict";

let buttons = document.querySelectorAll(".response-area button");

buttons.forEach(button => button.addEventListener("click", function() {
    let input = document.querySelector("input[name='Response']");
    input.value = this.value;
    
    document.getElementById("FormSubmitButton").click();
}));
</script>

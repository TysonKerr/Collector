<style>
    html, body, #content, .main_container {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    .main_container {
        display: table;
        font-size: 120%;
    }
    .main_container > div {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
    }
    
    .cue_container { 
        margin: 20px;
        font-size: 200%;
    }
    
    .response_container button {
        padding: 4px 8px;
        margin: 40px;
    }
</style>
<div class="main_container"><div>
    <div class="prompt">Was this item on the list of items to pack for the camping trip?</div>
    <div class="cue_container"><?= $cue ?></div>
    <div class="response_container">
        <button type="button">YES</button>
        <button type="button">NO</button>
    </div>
</div></div>

<div class="hidden">
    <input type="hidden" name="Response" id="response">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<script>

document.querySelectorAll(".response_container button").forEach(button => {
    button.addEventListener("click", function() {
        document.getElementById("response").value = this.innerHTML;
        document.getElementById("FormSubmitButton").click();
    });
});

</script>

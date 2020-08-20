<div class="hidden">
    You shouldn't be seeing this...
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<script>
"use strict";

// submit trial immediately
COLLECTOR.experiment["<?= $trialType ?>"] = function() {
    $("#FormSubmitButton").click();
};
</script>

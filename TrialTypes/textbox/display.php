<style>
    #content {
        max-width: 700px;
    }
    .question {
        font-size: 125%;
        margin-bottom: 2em;
        text-align: left;
    }
    .option-list label {
        display: block;
        text-align: left;
    }
    textarea {
        max-width: 100%;
    }
</style>

<p class="question"><?= $text ?></p>

<div><textarea rows="5" cols="60" name="Response"></textarea></div>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
</div>

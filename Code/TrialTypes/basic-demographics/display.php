<style>
<?= file_get_contents(__DIR__ . '/custom_style.css') ?>
</style>

<fieldset class="basicInfo">
    <legend><h1>Basic Demographics</h1></legend>
    
    <section class="radioButtons">
        <h3>Gender</h3>
        <label><input name="Gender" type="radio" value="Male"   required/>Male</label>
        <label><input name="Gender" type="radio" value="Female" required/>Female</label>
        <label><input name="Gender" type="radio" value="Other"  required/>Other</label>
    </section>
    
    <section>
        <label>
            <h3>Age</h3>
            <input name="Age" class="wide collectorInput" type="text"
            pattern="[0-9][0-9]" value="" autocomplete="off" required/>
        </label>
    </section>
    
    <section>
        <label>
            <h3>Birthday</h3>
            <input name="Birthday"type="date"
            value="" autocomplete="off" required/>
        </label>
    </section>
    
    <div id="age-warning" class="invis">
        Your age and birthday do not match up. Are you sure you entered them correctly?
    </div>
    
    <section>
        <label>
            <h3>Ethnicity</h3>
            <select name="Race" required class="wide collectorInput">
                <option value="" default selected>Select one</option>
                <option>American Indian/Alaskan Native</option>
                <option>Asian/Pacific Islander</option>
                <option>Black</option>
                <option>White</option>
                <option>Other/unknown</option>
            </select>
        </label>
    </section>
</fieldset>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
</div>

<script>
<?= file_get_contents(__DIR__ . '/custom_script.js') ?>
</script>

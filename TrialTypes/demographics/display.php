<fieldset class="basicInfo">
    <legend><h1>Basic Information</h1></legend>
    
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
            <h3>Education</h3>
            <select name="Education" class="wide collectorInput" required>
                <option value="" default selected>Select Level</option>
                <option>Some High School</option>
                <option>High School Graduate</option>
                <option>Some College, no degree</option>
                <option>Associates degree</option>
                <option>Bachelors degree</option>
                <option>Graduate degree (Masters, Doctorate, etc.)</option>
            </select>
        </label>
    </section>
    
    <!-- <section class="radioButtons">
        <h3>Are you Hispanic?</h3>
        <label><input name="Hispanic" type="radio" value="Yes"   required/>Yes</label>
        <label><input name="Hispanic" type="radio" value="No"    required/>No</label>
    </section> -->
    
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
    
    <section class="radioButtons">
        <h3>Do you speak english fluently?</h3>
        <label><input name="Fluent" type="radio" value="Yes"   required/>Yes</label>
        <label><input name="Fluent" type="radio" value="No"    required/>No</label>
    </section>
    
    <section>
        <label>
            <h3>At what age did you start learning English?</h3>
            <input name="AgeEnglish" type="text" value="" autocomplete="off" class="wide collectorInput"/>
            <div class="small shim">If English is your first language please enter 0.</div>
        </label>
    </section>
    
    <section>
        <label>
            <h3>What is your country of residence?</h3>
            <input name="Country" type="text" value="" autocomplete="off" class="wide collectorInput"/>
        </label>
    </section>
</fieldset>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
</div>

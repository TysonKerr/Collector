<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    require 'initiateCollector.php';
    
    $title = 'Basic Information';
    require $_PATH->get('Header');
?>
<style>    
    #content {
        width:auto;
        min-width: 400px;
        /*Make the flexchild, form, fit the basic info content size*/
    }
    
    #age-warning {
        margin-left: 2.5em;
        font-size: 125%;
        color: #600;
        width: 400px;
    }
    
    .invis { visibility: hidden; }
</style>
<form id="content" class="basicInfo" name="Demographics"
      action="<?= $_PATH->get('Basic Info Record') ?>" method="post" autocomplete="off">
    
    <fieldset>
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
        
        
        <section>
            <button id="submit-button" class="collectorButton">Submit Basic Info</button>
        </section>
        
    </fieldset>
</form>

<script>
"use strict";

var get_input = function(name) {
    return document.querySelector(`input[name='${name}']`);
};

var get_birthday_bounds = function(age) {
    let date = new Date();
    date.setFullYear(date.getFullYear() - age - 1);
    let min = date.valueOf();
    date.setFullYear(date.getFullYear() + 1);
    let max = date.valueOf();
    return [min, max];
}

var check_age_and_birthday = function() {
    let age = get_input("Age").value - 0;
    let birthday = get_input("Birthday").value;
    let birthday_timestamp = Date.parse(birthday);
    
    if (Number.isNaN(birthday_timestamp) || Number.isNaN(age)) return;
    
    let birthday_bounds = get_birthday_bounds(age);
    let birthday_within_bounds = (birthday_timestamp >= birthday_bounds[0]
        && birthday_timestamp <= birthday_bounds[1]);
    let warning = document.getElementById("age-warning");
    let button = document.getElementById("submit-button");
    
    if (birthday_within_bounds) {
        warning.classList.add("invis");
        button.disabled = false;
    } else {
        warning.classList.remove("invis");
        button.disabled = true;
    }
};

document.addEventListener("input", check_age_and_birthday);
</script>

<?php
    require $_PATH->get('Footer');

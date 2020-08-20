<?php
/*
$settings = [
    'alien_min_delay' => 0.5,
    'alien_max_delay' => 1,
    'alien_appear_time' => 0.5,
    'alien_catch_time' => 1.0,
    'alien_final_study_time' => 1,
    'feedback_time' => 2,
    'left_key' => 'ArrowLeft',
    'right_key' => 'ArrowRight'
];
$settings = json_encode($settings);
$settings = substr($settings, 1, -1);
*/
?><style>
    #content {
        margin: 0;
        padding: 0;
        height: 100%;
        width: 100%;
    }
    #instructions, #practice-zone {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    #instructions > div, #practice-zone > div {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
        width: 100%;
        height: 100%;
    }
    #instructions {
        height: 20%;
        line-height: 1.5em;
    }
    #practice-zone {
        height: 75%;
        background-color: #AAA;
    }
    #practice-zone .practicing {
        background-color: white;
    }
    #instructions > div > div {
        display: inline-block;
        text-align: left;
        max-width: 700px;
    }
    #cache {
        width: 1px;
        height: 1px;
        opacity: 0.01;
        overflow: hidden;
    }
    #practice-zone > div > div:not(.shown) {
        display: none;
    }
    #practice-container {
        position: relative;
        display: inline-block;
    }
    #alien-container {
        position: absolute;
        width: 50%;
        top: 0;
        bottom: 0;
        height: 100%;
    }
    #alien-container > div {
        display: table;
        height: 100%;
        width: 100%;
    }
    #alien-container > div > div {
        display: table-cell;
        text-align: center;
        vertical-align: middle;
    }
    #alien-container.right {
        left: 50%;
    }
    #alien-container.centered {
        left: 25%;
    }
    #practice-alien {
        object-fit: contain;
        max-width: 50%;
    }
    #practice-alien:not(.shown) {
        display: none;
    }
    #practice-context {
        max-width: 100%;
    }
    #failure-feedback {
        max-width: 600px;
        line-height: 1.5em;
        background-color: white;
        margin: auto;
        padding: 30px;
        font-size: 150%;
    }
    #end-experiment {
        max-width: 600px;
        line-height: 1.5em;
        background-color: white;
        margin: auto;
        padding: 30px;
    }
</style>

<div id="instructions"><div><div><?php echo $text; ?></div></div></div>

<div id="cache">
    <img src="../Experiment/Images/Practice/ca1.png">
    <img src="../Experiment/Images/Practice/ca2.png">
    <img src="../Experiment/Images/Practice/ca3.png">
    <img src="../Experiment/Images/Practice/ca4.png">
    <img src="../Experiment/Images/Practice/cliff1.jpg">
    <img src="../Experiment/Images/Practice/cliff2.jpg">
    <img src="../Experiment/Images/Practice/rocks1.jpg">
    <img src="../Experiment/Images/Practice/rocks2.jpg">
</div>

<div id="practice-zone"><div>
    <div id="start-practice-area" class="shown"><button type="button" id="start-button">Click here to practice</button></div>
    
    <div id="practice-container">
        <img id="practice-context" src="">
        <div id="alien-container"><div><div><img id="practice-alien" src=""></div></div></div>
    </div>
    
    <div id="failure-feedback">
        You missed the alien! Please respond more quickly and accurately next time! (Remember, use the left and right arrow keys to catch the alien)
    </div>
    
    <div id="end-experiment">
        Unfortunately, the experiment required the previous task to be completed successfully, so it will be discontinued here. Please submit the
        verification code <strong>DK4Z-<?= $_SESSION['ID'] ?></strong> to receive reimbursement for your time so far.
    </div>
    
    <div id="practice-done">
        <div class="hidden">
            <input name="Cycles_Practiced" value="0">
            <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
        </div>
    </div>
</div></div>

<script>

var settings = {<?= $settings ?>};

function add_event(selector, trigger, callback) {
    document.querySelector(selector).addEventListener(trigger, callback);
}

function get_random_num(min, max) {
    let num_min = Number(min);
    let num_max = Number(max);
    
    return Math.random() * (num_max - num_min) + num_min;
};

function submit_trial() {
    $("#FormSubmitButton").click();
};

var practice = {
    allow_catch_inputs: false,
    alien_catch_response: null,
    alien_catch_successful: false,
    failures_this_cycle: 0,
    cycles: 0,
    
    init: function() {
        this.alien_img = document.querySelector("#practice-alien");
        this.context_img = document.querySelector("#practice-context");
        this.alien_container = document.querySelector("#alien-container");
        this.practice_container = document.querySelector("#practice-container");
        this.feedback = document.querySelector("#failure-feedback");
        
        document.querySelector("#start-practice-area").classList.add("hidden");
        document.querySelector("#practice-zone").classList.add("practicing");
        document.addEventListener("keydown", e => this.keydown(e));
        this.start_new_cycle();
    },
    
    keydown: function(e) {
        if (!this.allow_catch_inputs) return;
        if (e.key !== settings.left_key && e.key !== settings.right_key) return;
        if (this.alien_catch_response !== null) return;
        
        let response = e.key === settings.left_key ? "left" : "right";
        
        this.alien_catch_response = response;
        this.alien_catch_successful = this.alien_container.classList.contains(response);
    },
    
    start_new_cycle: function() {
        this.failures_this_cycle = 0;
        this.cycles++;
        this.setup_new_cycle();
        this.start_next_trial();
    },
    
    setup_new_cycle: function() {
        let alien_locations = this.get_randomized_locations();
        
        this.trials = [
            {alien: "ca1.png", context: "cliff1.jpg", location: alien_locations[0]},
            {alien: "ca2.png", context: "cliff2.jpg", location: alien_locations[1]},
            {alien: "ca3.png", context: "rocks1.jpg", location: alien_locations[2]},
            {alien: "ca4.png", context: "rocks2.jpg", location: alien_locations[3]},
        ];
    },
    
    start_next_trial: function() {
        let trial = this.trials.shift();
        this.allow_catch_inputs = false;
        this.alien_catch_response = null;
        this.alien_catch_successful = false;
        this.alien_img.classList.remove("shown");
        this.alien_img.src = "../Experiment/Images/Practice/" + trial.alien;
        this.context_img.src = "../Experiment/Images/Practice/" + trial.context;
        this.alien_container.classList.remove("left", "right");
        this.alien_container.classList.add(trial.location);
        this.practice_container.classList.add("shown");
        this.feedback.classList.remove("shown");
        this.show_alien_after_delay();
    },
    
    show_alien_after_delay: function() {
        let alien_delay = get_random_num(settings.alien_min_delay, settings.alien_max_delay);
        COLLECTOR.timer(alien_delay, e => this.show_alien());
    },
    
    show_alien: function() {
        this.alien_img.classList.add("shown");
        this.allow_catch_inputs = true;
        this.alien_catch_response = null;
        COLLECTOR.timer(settings.alien_appear_time, e => this.hide_alien());
        COLLECTOR.timer(settings.alien_catch_time, e => this.end_alien_catch());
    },
    
    hide_alien: function() {
        this.alien_img.classList.remove("shown");
    },
    
    end_alien_catch: function() {
        if (!this.allow_catch_inputs) return;
        
        this.allow_catch_inputs = false;
        this.hide_alien();
        COLLECTOR.timer(0.5, e => this.show_catch_result());
    },
    
    show_catch_result: function() {
        if (this.alien_catch_successful) {
            // this.show_centered_alien();
            this.end_trial();
        } else {
            this.give_failure_feedback();
            this.failures_this_cycle++;
        }
    },
    
    give_failure_feedback: function() {
        this.practice_container.classList.remove("shown");
        this.feedback.classList.add("shown");
        COLLECTOR.timer(settings.feedback_time, e => {
            this.feedback.classList.remove("shown");
            COLLECTOR.timer(0.5, e => this.end_trial());
        });
    },
    
    /*
    show_centered_alien: function() {
        this.alien_container.classList.remove("left", "right");
        this.alien_container.classList.add("centered");
        this.alien_img.classList.add("shown");
        COLLECTOR.timer(settings.alien_final_study_time, e => this.end_trial());
    },
    */
    
    end_trial: function() {
        this.practice_container.classList.remove("shown");
        this.feedback.classList.remove("shown");
        this.alien_container.classList.remove("left", "right", "centered");
        
        if (this.trials.length === 0) {
            // check if they succeeeded every time. if not, redo everything. otherwise, submit trial
            document.querySelector("input[name='Cycles_Practiced']").value = this.cycles;
            submit_trial();
        } else {
            this.start_next_trial();
        }
    },
    
    end_experiment: function() {
        document.querySelector("#end-experiment").classList.add("shown");
    },
    
    get_randomized_locations: function() {
        let locations = ["left", "left", "right", "right"];
        this.shuffle_array(locations);
        return locations;
    },
    
    // https://stackoverflow.com/questions/2450954/how-to-randomize-shuffle-a-javascript-array
    shuffle_array: function(arr) {
        var currentIndex = arr.length, temporaryValue, randomIndex;

        // While there remain elements to shuffle...
        while (0 !== currentIndex) {
            // Pick a remaining element...
            randomIndex = Math.floor(Math.random() * currentIndex);
            currentIndex -= 1;
            
            // And swap it with the current element.
            temporaryValue = arr[currentIndex];
            arr[currentIndex] = arr[randomIndex];
            arr[randomIndex] = temporaryValue;
        }
    }
};

add_event("#start-button", "click", e => practice.init());

</script>

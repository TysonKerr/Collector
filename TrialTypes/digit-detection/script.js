"use strict";

const digit_detection = {
    tone_interval: 1000, // 3 tones are played per word, so this affects word interval too
    trial_display_time: 2500,
    sequence_position: 0,
    prev_odd_digits: 0,
    data: {
        response_timestamps: [],
        triple_rts: [],
        missed: 0,
        caught: 0,
        false_positives: 0
    },
    made_response: false,
    response_rt: null,
    tone_timestamp: null,
    
    init: function(digit_sequence) {
        this.digit_sequence = digit_sequence;
        document.getElementById("start-btn").style.display = "none";
        this.trial_loop = setInterval(e => this.advance_position(), this.tone_interval);
        this.advance_position();
        document.addEventListener("keydown", e => this.process_keydown(e));
    },
    
    advance_position: function() {
        if (this.sequence_position > 0) {
            this.score_tone_response();
        }
        
        if (this.sequence_position === 60) {
            this.end_trial();
            window.clearInterval(this.trial_loop);
            return;
        }
        
        if (this.sequence_position % 3 === 0) {
            this.show_next_trial();
        }
        
        this.play_current_digit();
        
        ++this.sequence_position;
    },
    
    end_trial: function() {
        for (const key in this.data) {
            const input_name = "tone_" + key;
            let val;
            
            if (Array.isArray(this.data[key])) {
                val = this.data[key].join("|");
            } else {
                val = this.data[key];
            }
            
            COLLECTOR.add_input(input_name, val);
        }
        
        this.submit_trial();
    },
    
    submit_trial: function() {
        document.getElementById("FormSubmitButton").click();
    },
    
    show_next_trial: function() {
        this.hide_current_trial(); // hide previous trial, if display time is longer than 3 tones
        const trial_index = 1 + this.sequence_position / 3;
        
        document.querySelector(`.trial-container:nth-child(${trial_index})`)
                .classList.add("current");
                
        setTimeout(e => this.hide_current_trial(), this.trial_display_time);
    },
    
    hide_current_trial: function() {
        let current = document.querySelector(".current");
        if (current !== null) current.classList.remove("current");
    },
    
    play_current_digit: function() {
        if (this.sequence_position >= this.digit_sequence.length) return;
        const digit = this.digit_sequence[this.sequence_position];
        this.play_digit(digit);
    },
    
    play_digit: function(digit) {
        if (digit % 2 === 1) {
            this.prev_odd_digits++;
        } else {
            this.prev_odd_digits = 0;
        }
        
        this.made_response = false;
        this.response_rt = null;
        this.tone_timestamp = COLLECTOR.getRT();
        let audio = document.querySelector(`.digit${digit}`);
        audio.currentTime = 0; // fixes bug where audio files longer than 1 second
                               // wouldnt start over when calling play()
        audio.play();
    },
    
    score_tone_response: function() {
        if (this.prev_odd_digits === 3) {
            if (this.made_response) {
                ++this.data.caught;
                this.data.triple_rts.push(this.response_rt);
            } else {
                ++this.data.missed;
                this.data.triple_rts.push("no response");
            }
        } else if (this.made_response) {
            ++this.data.false_positives;
        }
    },
    
    process_keydown: function(e) {
        if (this.made_response) return;
        if (e.code === "Space") this.save_response();
    },
    
    save_response: function() {
        const timestamp = COLLECTOR.getRT();
        this.data.response_timestamps.push(timestamp);
        this.made_response = true;
        this.response_rt = Math.round((timestamp - this.tone_timestamp) * 1000) / 1000;
    }
};

document.getElementById("start-btn").addEventListener("click", e => {
    digit_detection.init(window.digit_sequence);
});

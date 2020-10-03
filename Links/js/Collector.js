"use strict";
/**
 *  JS for Collector
 *  ver. 1.0.0
 *  by Adam Blake - adamblake@g.ucla.edu
 *
 *  Collector is program for running experiments on the web
 *  Copyright 2012-2013 Mikey Garcia & Nate Kornell
 *
 *
 *  Notes on this file:
 *
 *  This Javascript uses a technique called DOM-based Routing. You can read more about how it works
 *  here: http://viget.com/inspire/extending-paul-irishs-comprehensive-dom-ready-execution
 *
 *  Here are the basics:
 *  1. All JS code is included in one file in an object-oriented framework
 *  2. The HTML <body> "data-controller" and "data-action" attributes map to keys in the object literal
 *  3. On $(window).load, we use functions to run the appropriate bits of code. In order they are:
 *       i.  Common -> init
 *      ii.  [data-controller] -> init
 *     iii.  [data-controller] -> [data-action]
 *
 *  Note: step 3.ii. will only occur if a data-controller is specified in the HTML and 3.iii. will only
 *        occur if both a data-controller AND data-action have been specified
 *
 *  Using this method, while a bit complex, allows us to include page-specific JS and shared JS in a
 *  single file. This file will be cached on the first visit to the site, so in the end we reduce the
 *  load times of subsequent pages, and we do it in a nicely organized file.
 *
 *  Happy coding! --Adam
 *
 */

const COLLECTOR = {

    autofocus: true,
    /**
     *  Sets starting time to a property we can access anywhere in the namespace
     */
    startTime: Date.now(),
    
    add_input: function(name, val) {
        $(`<input type="hidden" name="${name}" value="${val}">`).appendTo("form");
    },
    
    get_submit_button: function() {
        const btn = document.getElementById("FormSubmitButton");
        
        if (btn !== null) return btn;
        
        return this.create_form_submit_button();
        
    },
    
    create_form_submit_button: function() {
        const btn = document.createElement("button");
        btn.type = "submit";
        btn.style.display = "none";
        document.querySelector("form").appendChild(btn);
        return btn;
    },

    /**
     *  Timer function
     *
     *  This countdown timer is for any case where you would want to timeout, like in timed trials
     *  For other cases where you just want to get the elapsed time, use the "getTime" function
     *
     *  @param {Int}        timeUp: the amount of time the timer runs for
     *  @param {Function}   callback: the function you want to run when the timer stops
     *  @param {Object}     show (optional): if included, the timer will send it's current value to this element
     *
     *
     *  Example usage:
     *      COLLECTOR.timer(2, function() {
     *          $("form").submit();
     *      }, $("#countdown"));
     */
    timer: function (timeUp, callback, show) {
        // waitPercent is the percentage of timeRemaining that will be used for each setTimeout
        // waitPercent should be greater than 0, but less than 1
        // cap is the lowest number of ms allowed per interval.
        // for HTML5 browsers, 4ms is the setTimeout minimum, so the cap should be at least 4.
        // for both of these values, increasing them lowers both accuracy and processing requirements
        var waitPercent = .5,
            cap         = 4,
            goal        = Date.now() + timeUp*1000,
            lastWait    = cap*2,
            lastAim     = (cap*2)/waitPercent,
            id;

        function instance() {
            var timeRemaining = goal - Date.now(),
                timeFormatted;

            // stop timer at the allotted time
            if (timeRemaining <= cap) {
                return callback();
            }

            if (show) {
                timeFormatted = Math.round(timeRemaining/100) / 10;
                if (Math.round(timeFormatted) == timeFormatted) { timeFormatted += '.0'; }
                if (show.is('input')) {
                    show.val(timeFormatted);
                } else {
                    show.html(timeFormatted);
                }
                timeRemaining = Math.min(20,timeRemaining);
            } else {
                timeRemaining = Math.min(timeRemaining,10000);
            }

            if (timeRemaining <= lastWait) {
                timeRemaining = cap;
            } else if (timeRemaining <= lastAim) {
                timeRemaining = timeRemaining - Math.floor(cap*1.5);
            } else {
                timeRemaining *= waitPercent;
            }
            timeRemaining = Math.max(cap, Math.floor(timeRemaining));

            // run the timer again, using a percentage of the time remaining
            id = setTimeout(function() { instance(); }, timeRemaining);
        }

        // start the timer
        instance();
        
        return {
            cancel: function() {
                window.clearTimeout(id);
            }
        };
    },

    getRT: function() {
        var currentTime = Date.now();
        return (Date.now() - this.startTime) / 1000;
    },

    common: {
        init: function() {
            var startTime = COLLECTOR.startTime;

            // these happen immediately on load
            $(':input:not(:radio):enabled:visible:not(.logout)').first().focusWithoutScrolling(); // focus cursor on first input
            $("#loadingForm").submit();                         // submit form to advance page

            // allows for the collapsing of readable() outputs
            $(".collapsibleTitle").click( function() {
                $(this).parent().children().not(".collapsibleTitle").toggle(350);
            });
            // starts them collapsed
            $(document).ready( function() {
                $(".collapsibleTitle").parent().children().not(".collapsibleTitle").toggle(350);
                window.scrollTo(0, 0);
            });
            
            // forceNumeric
            $(".forceNumeric").forceNumeric();
            // stop forms from submitting more than once
            $("form").preventDoubleSubmission();
            
            // ensures that all forms have autocomplete disabled
            $(document).ready(function () {
                $("form").attr('autocomplete', 'off');
            });
            
            var pre_dumps = $(".dump pre");
            var pre_dump_widths = pre_dumps.map(function(e) { return $(this).width(); }).get();
            var width_max = Math.max(...pre_dump_widths);
            pre_dumps.width(width_max);
        }
    },

    experiment: {
        init: function() {
            if (document.querySelector(".recording-errors") !== null) return;
            // if we are recording data, we are about to redirect the page
            // so we dont need to actually run any client-side code
            if (typeof COLLECTOR.trial_values === "undefined") {
                COLLECTOR.trial_values = {
                    max_time: "user",
                    min_time: "user"
                };
            }
            
            var trialTime = parseFloat(COLLECTOR.trial_values.max_time),
                minTime   = parseFloat(COLLECTOR.trial_values.min_time),
                fsubmit   = $("#FormSubmitButton");
            
            // check for window focus every .2 seconds
            var focusChecks = 0, focusCount  = 0, focusProp;
            
            window.setInterval(e => {
                focusChecks++;
                
                if (document.hasFocus()) focusCount++;
            }, 20);

            $("form").submit( function(event){
                $("#content").addClass("invisible"); // hide content
                focusProp = Math.round((focusCount/focusChecks)*1000) / 1000;
                COLLECTOR.add_input("Trial_Focus",            focusProp);
                COLLECTOR.add_input("Trial_Duration",         COLLECTOR.getRT());
                COLLECTOR.add_input("Trial_Window_Width",     window.innerWidth);
                COLLECTOR.add_input("Trial_Window_Height",    window.innerHeight);
                COLLECTOR.add_input("Trial_Submit_Timestamp", Date.now() / 1000);
                // run any custom defined submit functions
                if (typeof window.on_submit === "function") {
                    return on_submit(event);
                }
            });
            
            COLLECTOR.add_input("Trial_Start_Timestamp", Date.now() / 1000);
            COLLECTOR.add_input("Trial_Timestamp_First_Keypress", "-1");
            COLLECTOR.add_input("Trial_Timestamp_Last_Keypress", "-1");

            // show trial content
            if (isNaN(trialTime) || trialTime > 0) {
                $("#content").removeClass("invisible");                     // unhide trial contents
                COLLECTOR.startTime = Date.now();
                
                if (COLLECTOR.autofocus) {
                    $(':input:not(:radio,:checkbox):enabled:visible').first().focusWithoutScrolling();  // focus cursor on first input
                }
            } else {
                $("form").submit();
            }

            // start timers
            if (trialTime > 0) {
                COLLECTOR.timer(trialTime, function() {             // start the timer
                    // submit the form when time is up
                    $("form").submit();                         // see common:init "intercept form submit"
                }, false);                                      // run the timer (no minTime set)
                $(":input").addClass("noEnter");                // disable enter from submitting the trial
                $("textarea").removeClass("noEnter");           // allow textarea line returns
                if(isNaN(minTime) || minTime >= trialTime) {
                    fsubmit.addClass("invisible");                  // hide submit button
                }
            }

            if (!(isNaN(minTime)) && (isNaN(trialTime) || minTime < trialTime)) {
                fsubmit.prop("disabled", true);                 // disable submit button when minTime is set
                $(":input").addClass("noEnter");                // disable enter from submitting the trial
                $("textarea").removeClass("noEnter");               // allow line return in <textarea>
                $("form").addClass("WaitingForMinTime");
                COLLECTOR.timer(minTime, function() {           // run timer for minTime
                    fsubmit.prop("disabled", false);            // enable
                    $(":input").removeClass("noEnter");
                    $("form").removeClass("ComputerTiming")
                             .removeClass("WaitingForMinTime")
                             .addClass("UserTiming");
                    if ($("form").hasClass("submitAfterMinTime")) {
                        $("form").submit();
                    }
                }, false );
            }

            // disable 'noEnter' inputs and gather RTs for keypresses
            document.addEventListener("keydown", e => {
                if (e.keyCode === 13) {
                    if (e.target.classList.contains("noEnter")) {
                        e.preventDefault();
                    }
                } else {
                    // monitor and log first/last keypress
                    let first_keypress_inp = $("input[name='Trial_Timestamp_First_Keypress']");
                    
                    if (first_keypress_inp.val() == '-1') {                    // on first keypress
                        first_keypress_inp.val(COLLECTOR.getRT());
                    }
                    
                    let last_keypress_inp = $("input[name='Trial_Timestamp_Last_Keypress']");
                    last_keypress_inp.val(COLLECTOR.getRT());
                }
            });

            // prevent the backspace key from navigating back.
            // http://stackoverflow.com/questions/1495219/how-can-i-prevent-the-backspace-key-from-navigating-back
            $(document).on('keydown', function (event) {
              if (event.keyCode === 8) {
                var doPrevent = true;
                var types = ["text", "password", "file", "search", "email", "number", "date", "color", "datetime", "datetime-local", "month", "range", "search", "tel", "time", "url", "week"];
                var d = $(event.srcElement || event.target);
                var disabled = d.prop("readonly") || d.prop("disabled");
                if (!disabled) {
                  if (d[0].isContentEditable) {
                    doPrevent = false;
                  } else if (d.is("input")) {
                    var type = d.attr("type");
                    if (type) {
                      type = type.toLowerCase();
                    }
                    if (types.indexOf(type) > -1) {
                      doPrevent = false;
                    }
                  } else if (d.is("textarea")) {
                    doPrevent = false;
                  }
                }
                if (doPrevent) {
                  event.preventDefault();
                  return false;
                }
              }
            });
            
            if (typeof on_trial_start === "function") on_trial_start();
            
            multi_trial.init();
        },
    },
};

const UTIL = {
    exec: function( controller, action ) {
        var ns = COLLECTOR,
            action = (action === undefined) ? "init" : action;

        if (controller !== "" && ns[controller] && typeof ns[controller][action] == "function") {
            ns[controller][action]();
        }
    },

    init: function() {
        var body = document.body,
            controller = body.getAttribute("data-controller"),
            action = body.getAttribute("data-action");

        UTIL.exec("common");
        UTIL.exec(controller);
        UTIL.exec(controller, action);
    }
};

jQuery.fn.focusWithoutScrolling = function() {
    if ($(this).length === 0) return this;
    
    var parents = [], parentScrolls = [];
    var currentElement = $(this);
    
    while (currentElement[0] !== document && currentElement.scrollParent) {
        currentElement = currentElement.scrollParent();
        parents.push(currentElement);
        parentScrolls.push(currentElement.scrollTop());
    }
    
    this.focus();
    
    while (parents.length > 0) {
        currentElement = parents.pop();
        currentElement.scrollTop(parentScrolls.pop());
    }
    return this; //chainability
};

if (!Date.now) {
    Date.now = function now() {
        return new Date().getTime();
    };
}

jQuery.fn.forceNumeric = function () {
    // forceNumeric() plug-in implementation
    // I got forceNumeric from http://weblog.west-wind.com/posts/2011/Apr/22/Restricting-Input-in-HTML-Textboxes-to-Numeric-Values
    return this.each(function () {
        $(this).keydown(function (e) {
            var key = e.which || e.keyCode;
            
            if (!e.shiftKey && !e.altKey && !e.ctrlKey &&
             // numbers   
                key >= 48 && key <= 57 ||
             // Numeric keypad
                key >= 96 && key <= 105 ||
             // comma, period and minus, . on keypad
             // key == 190 || key == 188 || key == 109 || key == 110 ||
                key == 190 || key == 110 ||
             // Backspace and Tab and Enter
                key == 8 || key == 9 || key == 13 ||
             // Home and End
                key == 35 || key == 36 ||
             // left and right arrows
                key == 37 || key == 39 ||
             // Del and Ins
                key == 46 || key == 45)
                return true;
            
            return false;
        });
    });
};

// jQuery plugin to prevent double submission of forms
// http://stackoverflow.com/questions/2830542/prevent-double-submission-of-forms-in-jquery
jQuery.fn.preventDoubleSubmission = function() {
  $(this).on('submit',function(e){
    var $form = $(this);

    if ($form.data('submitted') === true) {
      // Previously submitted - don't submit again
      e.preventDefault();
    } else {
      // Mark it so that the next submit can be ignored
      $form.data('submitted', true);
    }
  });
  // Keep chainability
  return this;
};

$(window).on("load", (UTIL.init));

const multi_trial = {
    disabled: false,
    trial: 0,
    phase: 0,
    callbacks: {},
    phase_end_callbacks: {},
    
    trial_start: 0,
    phase_start: 0,
    
    ready_to_submit: false,
    phase_timer: null,
    
    init: function() {
        if (this.disabled) return;
        
        this.trial_count = document.querySelectorAll(".trial-container").length;
        
        if (this.trial_count > 0) {
            this.set_phase_submit_handler();
            this.start_trial();
        }
    },
    
    set_phase_submit_handler: function() {
        document.addEventListener("submit", e => {
            if (!this.ready_to_submit) {
                e.preventDefault();
                e.stopPropagation();
                this.end_phase();
            }
        }, true);
    },
    
    start_trial: function() {
        const container = this.get_trial_container();
        container.classList.add("current-trial");
        this.phase = 0;
        this.trial_start = performance.now();
        this.start_phase();
    },
    
    get_trial_container: function() {
        let index = this.trial + 1;
        return document.querySelector(`.trial-container:nth-child(${index})`);
    },
    
    start_phase: function() {
        const phase_name = this.get_phase_name(this.phase);
        
        if (phase_name === null) return this.end_trial();
        
        const container = this.get_phase_container(phase_name);
        this.phase_start = performance.now();
        this.prepare_timing(phase_name, container);
        container.classList.add("current-phase");
        this.focus_on_first_focusable(container);
        
        if (phase_name in this.callbacks) this.callbacks[phase_name]();
    },
    
    get_phase_name: function(index) {
        return this.get_phase_names()[index] || null;
    },
    
    get_phase_names: function() {
        const container = document.querySelector(".current-trial");
        return Array.from(container.children).map(this.get_element_phase_name)
            .filter(name => name);
    },
    
    get_element_phase_name: function(element) {
        for (let i = 0; i < element.classList.length; ++i) {
            let classname = element.classList[i];
            
            if (classname.substring(classname.length - 6) === "-phase") {
                return classname.substring(0, classname.length - 6);
            }
        }
        
        return null;
    },
    
    focus_on_first_focusable: function(container) {
        const element = this.get_first_focusable(container);
        
        if (element) element.focus();
    },
    
    get_first_focusable: function(container) {
        const focusable = Array.from(container.querySelectorAll("input, textarea, button"))
            .filter(this.element_is_focusable);
        
        return focusable.length > 0 ? focusable[0] : null;
    },
    
    element_is_focusable: function(element) {
        if (element.disabled) return false;
        
        const style = window.getComputedStyle(element);
        
        if (style.getPropertyValue("display") === "none") return false;
        if (style.getPropertyValue("visibility") === "hidden") return false;
        
        return true;
    },
    
    get_phase_container: function(name) {
        return document.querySelector(`.current-trial .${name}-phase`);
    },
    
    prepare_timing: function(phase_name, phase_container) {
        const settings = this.get_settings();
        
        if (phase_name in settings) {
            const phase_time = parseFloat(settings[phase_name]);
            
            if (!Number.isNaN(phase_time)) {
                this.phase_timer = COLLECTOR.timer(phase_time, e => this.end_phase());
                $(phase_container).find(":submit").addClass("invisible");
            }
        }
    },
    
    get_settings: function() {
        return COLLECTOR.trial_values.settings;
    },
    
    end_phase: function() {
        const phase_name = this.get_phase_name(this.phase);
        
        if (phase_name in this.phase_end_callbacks) {
            if (this.phase_end_callbacks[phase_name]() === false) return;
        }
        
        this.set_phase_duration(phase_name);
        this.cancel_phase_timer();
        document.querySelector(".current-phase").classList.remove("current-phase");
        ++this.phase;
        this.start_phase();
    },
    
    set_phase_duration: function(phase_name) {
        const duration_input = this.get_duration_input(phase_name);
        
        if (duration_input !== null) {
            duration_input.value = this.get_phase_duration();
        }
    },
    
    get_duration_input: function(phase_name) {
        return document.querySelector(`.current-phase .${phase_name}-duration`);
    },
    
    get_phase_duration: function() {
        return performance.now() - this.phase_start;
    },
    
    cancel_phase_timer: function() {
        if (this.phase_timer !== null) {
            this.phase_timer.cancel();
            this.phase_timer = null;
        }
    },
    
    end_trial: function() {
        document.querySelector(".current-trial").classList.remove("current-trial");
        ++this.trial;
        
        if (this.trial >= document.querySelectorAll(".trial-container").length) {
            this.submit_trials();
        } else {
            this.start_trial();
        }
    },
    
    submit_trials: function() {
        this.ready_to_submit = true;
        COLLECTOR.get_submit_button().click();
    }
};

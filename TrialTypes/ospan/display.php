<?php
    if (!isset($settings)) $settings = '';
    $settings = explode('|', $settings);
    $invertColors = false;
    $spans = '[3, 3, 3, 4, 4, 4, 5, 5, 5, 6, 6, 6, 7, 7, 7]';
    foreach ($settings as $setting) {
        if (strtolower(trim($setting)) === 'invertColors') $invertColors = true;
        if (($setting = removeLabel($setting, 'spans')) !== false) {
            $newSpans = rangeToArray($setting);
            foreach ($newSpans as $i => $spanLength) {
                $spanLength = (int) $spanLength;
                if ($spanLength < 1) {
                    continue 2;
                } else {
                    $newSpans[$i] = $spanLength;
                }
            }
            $spans = '[' . implode(', ', $newSpans) . ']';
        }
    }
?>
<style>
<?php if ($invertColors): ?>
    body { background-color: black; color: white; }
    
    .ospanButton { border-color: white; background-color: #1A1A1A; }
    .ospanButton::selection { color: white; background-color: #1A1A1A; }
    .ospanButton:not(.disabled):hover { background-color: #222; }
    .ospanButton:hover { color: green; border-color: green; }
    .ospanButton:hover::selection { color: green; }
    
    .letterResponseDisplay { border-color: white; }
    
    .instructButtonArea button { border-color: #DDD; background-color: #AAA; color: black; }
    .instructButtonArea button:not(.disabled):hover { background-color: #999; }
<?php else: ?>
    body { background-color: white; color: black; }
    
    .ospanButton { border-color: black; background-color: #CCC; }
    .ospanButton::selection { color: black; background-color: #CCC; }
    .ospanButton:not(.disabled):hover { background-color: #BBB; }
    .ospanButton:hover { color: #050; border-color: #050; }
    .ospanButton:hover::selection { color: #050; }
    
    .letterResponseDisplay { border-color: black; }
    
    .instructButtonArea button { border-color: #666; background-color: #CCC; color: black; }
    .instructButtonArea button:not(.disabled):hover { background-color: #BBB; }
<?php endif; ?>

    .ospanDisplayFont { font-family: sans-serif; }
    .ospanText { font-family: serif; }
    
    #content { padding: 0; width: 100%; }
    
    #ospanContainer { text-align: center; 
        -webkit-touch-callout: none; /* iOS Safari */
        -webkit-user-select: none;   /* Chrome/Safari/Opera */
        -khtml-user-select: none;    /* Konqueror */
        -moz-user-select: none;      /* Firefox */
        -ms-user-select: none;       /* Internet Explorer/Edge */
        user-select: none;           /* Non-prefixed version, currently
                                        not supported by any browser */
    }
    #ospanContainer > div { display: none; }
    
    .instructions { max-width: 700px; margin: auto; font-size: 150%; display: none; text-align: left; }
    .letterDisplayContainer { font-size: 400%; }
    
    .letterResponsePrompt { font-size: 80%; max-width: 575px; margin: auto; }
    
    .ospanButton { padding: 5px 14px; border-style: solid; border-width: 2px; cursor: pointer; }
    .ospanButton.disabled { color: gray; border-color: gray; cursor: not-allowed; }
    .ospanButton.disabled::selection { color: gray; }
    
    .letterRespondContainer { font-size: 200%; }
    .letterResponseGrid { font-size: 150%; }
    .letterResponseRow { margin: 20px auto; width: 100%; max-width: 400px; }
    .letterResponseCell { display: inline-block; width: 16%; padding: 3px 0; margin: 1px 3%; min-width: 45px; }
    
    .blankResponse { display: inline-block; margin: 5px; }
    
    .letterResponseDisplay { height: 1.2em; border-width: 2px; border-style: solid; width: 350px; margin: 20px auto; letter-spacing: 0.3em; max-width: 90%; }
    
    .letterResponseSubmitArea > div { display: inline-block; margin: 5px; width: 110px; }
    
    .spanLetterFeedback { font-size: 150%; }
    .spanMathFeedback { font-size: 150%; }
    .spanMathPercentage { font-size: 150%; }
    .spanMathPercentage.warning { color: red; }
    
    .mathProblem { font-size: 200%; margin: 0 0 100px; }
    .mathInstructions { font-size: 150%; }
    
    .solution { font-size: 300%; margin-bottom: 100px; }
    .mathResponseArea > div { font-size: 200%; display: inline-block; width: 100px; margin: 0 50px; }
    .mathFeedback { font-size: 250%; margin-top: 100px; }
    
    .practiceMathFeedback { font-size: 150%; }
    
    .instructButtonArea { text-align: center; margin: 15px auto 0; font-family: sans-serif; }
    .instructButtonArea button { margin: 15px; min-width: 110px; border-style: outset; border-width: 2px; cursor: pointer; border-radius: 4px; }
    .instructButtonArea button:not(.disabled):active { border-style: inset; }
    .instructButtonArea button.disabled { cursor: not-allowed; opacity: 0.5; }
    .instructButtonArea button:focus { outline: 0; }
</style>
<?php
    $instructButtons = 
        '<div class="instructButtonArea">'
      .     '<button type="button" class="prevInstruct">Previous</button>'
      .     '<button type="button" class="nextInstruct">Next</button>'
      . '</div>';
?>
<div id="ospanContainer" class="ospanDisplayFont">
    <div class="initialInstructionsContainer instructionsContainer ospanText">
        <div class="instructions">
            <p>In this experiment you will try to memorize letters you see on the screen while you also solve simple math problems.</p>
            <p>In the next few minutes, you will have some practice to get you familiar with how the experiment works.</p>
            <p>We will begin by practicing the letter part of the experiment.</p>
            <?= $instructButtons ?>
        </div>
        <div class="instructions">
            <p>For this practice set, letters will appear on the screen one at a time.</p>
            <p>Try to remember each letter in the order presented.</p>
            <p>After 2-3 letters have been shown, you will see a screen listing 12 possible letters.</p>
            <p>Your job is to select each letter in the order presented. To do this, use the mouse to select the letter. The letters you select will appear at the bottom of the screen.</p>
            <?= $instructButtons ?>
        </div>
        <div class="instructions">
            <p>When you have selected all the letters, and they are in the correct order, hit the SUBMIT box at the bottom right of the screen.</p>
            <p>If you make a mistake, hit the CLEAR box to start over.</p>
            <p>If you forget one of the letters, click the BLANK box to mark the spot for the missing letter.</p>
            <p>Remember, it is very important to get the letters in the same order as you see them. If you forget one, use the BLANK box to mark the position.</p>
            <p>When you're ready, click "Begin" to start the letter practice.</p>
            <?= $instructButtons ?>
        </div>
    </div>
    
    <div class="mathInstructionsContainer instructionsContainer ospanText">
        <div class="instructions">
            <p>Now you will practice doing the math part of the experiment.</p>
            <p>A math problem will appear on the screen, like this:</p>
            <div style="text-align: center;" class="ospanDisplayFont">(2 * 1) + 1 = ?</div>
            <p>As soon as you see the math problem, you should compute the correct answer.</p>
            <p>In the above problem, the answer 3 is correct.</p>
            <p>When you know the correct answer, you will click the mouse.</p>
            <?= $instructButtons ?>
        </div>
        <div class="instructions">
            <p>You will see a number displayed on the next screen, along with a box marked TRUE and a box marked FALSE.</p>
            <p>If the number on the screen is the correct answer to the math problem, click on the TRUE box with the mouse.</p>
            <p>If the number is not the correct answer, click on the FALSE box.</p>
            <p>For example, if you see the problem</p>
            <div style="text-align: center;" class="ospanDisplayFont">(2 * 2) + 1 = ?</div>
            <p>and the number on the following screen is 5, click the TRUE box, because the answer is correct.</p>
            <p>If you see the problem</p>
            <div style="text-align: center;" class="ospanDisplayFont">(2 * 2) + 1 =  ?</div>
            <p>and the number on the next screen is 6, click the FALSE box, because the correct answer is 5, not 6.</p>
            <p>After you click on one of the boxes, the computer will tell you if you made the right choice.</p>
            <?= $instructButtons ?>
        </div>
        <div class="instructions">
            <p>It is VERY important that you get the math problems correct. It is also important that you try and solve the problem as quickly as you can.</p>
            <p>When you're ready, click "Begin" to try some practice problems.</p>
            <?= $instructButtons ?>
        </div>
    </div>
    
    <div class="bothInstructionsContainer instructionsContainer ospanText">
        <div class="instructions">
            <p>Now you will practice doing both parts of the experiment at the same time.</p>
            <p>In the next practice set, you will be given one of the math problems. Once you make your decision about the math problem, a letter will appear on the screen. Try and remember the letter.</p>
            <p>In the previous section where you only solved math problems, the computer computed your average time to solve the problems. If you take longer than your average time, the computer will automatically move you onto the next letter part, thus skipping the True or False part and will count that problem as a math error.</p>
            <p>Therefore it is VERY important to solve the problems as quickly and as accurately as possible.</p>
            <?= $instructButtons ?>
        </div>
        <div class="instructions">
            <p>After the letter goes away, another math problem will appear, and then another letter.</p>
            <p>At the end of each set of letters and math problems, a recall screen will appear. Use the mouse to select the letters you just saw. Try your best to get the letters in the correct order.</p>
            <p>It is important to work QUICKLY and ACCURATELY on the math. Make sure you know the answer to the math problem before clicking to the next screen. You will not be told if your answer to the math problem is correct.</p>
            <p>After the recall screen, you will be given feedback about your performance regarding both the number of letters recalled and the percent correct on the math problems.</p>
            <?= $instructButtons ?>
        </div>
        <div class="instructions">
            <p>During the feedback, you will see your overall math accuracy for the entire experiment.</p>
            <p>It is VERY important for you to keep this at least at 85%.
            For our purposes, we can only use data where the participant was at least
            85% accurate on the math.</p>
            <p>Therefore, in order for you to be asked to come back for future experiments,
            you must perform at least at 85% on the math problems
            WHILE doing your best to recall as many letters as possible.</p>
            <p>Click "Begin" to try some practice problems.</p>
            <?= $instructButtons ?>
        </div>
    </div>
    
    <div class="finalInstructionsContainer instructionsContainer ospanText">
        <div class="instructions">
            <p>That is the end of the practice.</p>
            <p>The real trials will look like the practice trials you just completed.
            First you will get a math problem to solve, then a letter to remember.</p>
            <p>When you see the recall screen, select the letters in the order presented.
            If you forget a letter, click the BLANK box to mark where it should go.</p>
            <p>Some of the sets will have more math problems and letters than others.</p>
            <p>It is important that you do your best on both the math problems and
            the letter recall parts of this experiment.</p>
            <p>Remember on the math you must work as QUICKLY and ACCURATELY as possible.
            Also, remember to keep your math accuracy at 85% or above.</p>
            <p>Click "Begin" to begin the experiment.</p>
            <?= $instructButtons ?>
        </div>
    </div>
    
    <div class="practiceMathFeedback ospanText"></div>
    
    <div class="mathDisplayContainer">
        <div class="mathProblem"></div>
        <p class="mathInstructions ospanText">When you have solved the math problem, click the left mouse button to continue.</p>
    </div>
    
    <div class="mathRespondContainer">
        <div class="solution"></div>
        <div class="mathResponseArea"><div class="ospanButton">True</div><div class="ospanButton">False</div></div>
        <div class="mathFeedback"></div>
    </div>
    
    <div class="letterDisplayContainer"></div>
    
    <div class="letterRespondContainer">
        <div class="letterResponsePrompt ospanText">Select the letters in the order presented. Use the blank button to fill in forgotten items.</div>
        <div class="letterResponseGrid"></div>
        <div class="blankResponseArea"><span class="blankResponse ospanButton">Blank</span></div>
        <div class="letterResponseDisplay"></div>
        <div class="letterResponseSubmitArea">
            <div class="clearButton ospanButton">Clear</div>
            <div class="exitButton ospanButton">Submit</div>
        </div>
    </div>
    
    <div class="spanFeedbackContainer ospanText">
        <div class="spanLetterFeedback">You recalled <span class="letterRecall"></span> out of <span class="spanLength"></span> letters correctly.</div>
        <div class="spanMathFeedback"></div>
        <div class="spanMathPercentage"></div>
    </div>
</div>

<div class="hidden">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<script>
var OSpan = {
    Settings: {
        letters: "FHJKLNPQRSTY",
        practiceLetterSpans: [2, 2, 3, 3],
        practiceMathSpans: [
            // [1st number, 1st operation, 2nd num, 2nd op, 3rd num, solution]
            // eg. [4,'*',6,'-',2,12] => "(4*6) - 2 = ?" and "12" as the test
            [1,'*',2,'+',1,3],
            [1,'/',1,'-',1,2],
            [7,'*',3,'-',3,18],
            [4,'*',3,'+',4,16],
            [3,'/',3,'+',2,1],
            [2,'*',6,'-',4,6],
            [8,'*',9,'-',8,64],
            [4,'*',5,'-',5,11],
            [4,'*',2,'+',6,14],
            [4,'/',4,'+',7,12],
            [8,'*',2,'-',8,2],
            [2,'*',9,'-',9,9],
            [8,'/',2,'+',9,7],
            [3,'*',8,'-',1,23],
            [6,'/',3,'+',1,3],
        ],
        practiceBoth: [2, 2, 2],
        realSpans: <?= $spans ?>,
        
        mathPercentageRequired: 85,
        
        time: {
            preLetter: 0.2,
            letter: 0.8,
            preFeedback: 0.5,
            feedback: 1.5,
            preMathProblem: 0.5,
            preMathSolution: 0.5,
            mathFeedback: 0.8,
            preRecall: 0.5
        }
    },
    
    letters: null,
    mathTimeLimit: null,
    showingMath: false,
    allowMathResponse: false,
    giveMathFeedback: false, // set to true during the practice math phase
    mathStems: [],
    
    Stimuli: {
        practiceLetters: [],
        practiceMath: [],
        practiceBoth: [],
        realSpans: []
    },
    
    currentPhase: null,
    currentSpanGroup: null,
    currentSpan: null,
    currentTrial: null,
    phaseTimes: {},
    
    mathTotal: {
        count: 0,
        correct: 0
    },
    
    el: {},
    
    resp: [],
    
    currentSpanData: {},
    
    
    /* * * * * * * * * *
     * Helper Functions
     */
    getSequence: function(length) {
        var me = this;
        var seq = me.letters.slice();
        shuffle(seq);
        return seq.slice(0, length);
    },
    
    getDigit: function() {
        return Math.floor(Math.random()*9)+1;
    },
    
    getMathProblem: function() {
        var me = this;
        
        var mathProb, solution, randDigit;
        
        mathProb = me.mathStems.pop();
        if (mathProb[1] === '*') {
            solution = mathProb[0]*mathProb[2];
        } else {
            solution = mathProb[0]/mathProb[2];
        }
        
        randDigit = me.getDigit();
        if (solution - randDigit > 0) {
            if (Math.random() > 0.5) {
                mathProb.push('-');
                solution -= randDigit;
            } else {
                mathProb.push('+');
                solution += randDigit;
            }
        } else {
            mathProb.push('+');
            solution += randDigit;
        }
        
        mathProb.push(randDigit);
        
        if (Math.random() > 0.5) {
            // show correct answer
            mathProb.push(solution);
        } else {
            // show incorrect answer
            randDigit = me.getDigit();
            if (solution - randDigit > 0) {
                if (Math.random() > 0.5) {
                    mathProb.push(solution - randDigit);
                } else {
                    mathProb.push(solution + randDigit);
                }
            } else {
                mathProb.push(solution + randDigit);
            }
        }
        
        return mathProb;
    },
    
    createSpan: function(letters, includeMath) {
        var me = this;
        
        var i, len, math = null, trials = [];
        
        for (i=0, len=letters.length; i<len; ++i) {
            if (includeMath) {
                math = me.getMathProblem();
            }
            trials.push({
                letter: letters[i],
                math: math
            });
        }
        
        return trials;
    },
    
    createMathStems: function() {
        var i, j, mathStem, mathStems = [];
        for (i=1; i<=9; ++i) {
            for (j=1; j<=9; ++j) {
                mathStem = [i,"*",j];
                mathStems.push(mathStem);
                if (i%j === 0) {
                    mathStem = [i,"/",j];
                    mathStems.push(mathStem);
                }
            }
        }
        shuffle(mathStems);
        
        return mathStems;
    },
    
    createStimuli: function() {
        var me = this;
        
        me.letters = me.Settings.letters.split("");
        me.mathStems = me.createMathStems();
        me.Settings.realSpans = shuffle(me.Settings.realSpans);
        
        var i, j, length, sequence, k, span;
        for (i=0, j=me.Settings.practiceLetterSpans.length; i<j; ++i) {
            length = me.Settings.practiceLetterSpans[i];
            sequence = me.getSequence(length);
            span = me.createSpan(sequence, false);
            me.Stimuli.practiceLetters.push(span);
        }
        var mathSpan = [];
        for (i=0, j=me.Settings.practiceMathSpans.length; i<j; ++i) {
            mathSpan.push({letter: null, math: me.Settings.practiceMathSpans[i]});
        }
        me.Stimuli.practiceMath.push(mathSpan);
        for (i=0, j=me.Settings.practiceBoth.length; i<j; ++i) {
            length = me.Settings.practiceBoth[i];
            sequence = me.getSequence(length);
            span = me.createSpan(sequence, true);
            me.Stimuli.practiceBoth.push(span);
        }
        for (i=0, j=me.Settings.realSpans.length; i<j; ++i) {
            length = me.Settings.realSpans[i];
            sequence = me.getSequence(length);
            span = me.createSpan(sequence, true);
            me.Stimuli.realSpans.push(span);
        }
        
        return true;
    },
    
    prepareInstructions: function() {
        var me = this;
        
        $(".nextInstruct").on("click", function() {
            if ($(this).hasClass("disabled")) return;
            me.advanceInstructions();
        });
        
        $(".prevInstruct").on("click", function() {
            if ($(this).hasClass("disabled")) return;
            me.backInstructions();
        });
        
        $(".instructions:first-child .prevInstruct").addClass("disabled");
        $(".instructions:last-child  .nextInstruct").html("Begin");
    },
    
    prepareRecall: function() {
        var me = this;
        
        var letterTable = me.el.letterResp.find(".letterResponseGrid");
        
        var cols = 4;
        var rows = Math.ceil(me.letters.length / 3);
        var row, col, letterIndex, letter;
        
        var rowHtml, cellHtml;
        
        for (row=0; row<rows; ++row) {
            rowHtml = $("<div>");
            rowHtml.addClass("letterResponseRow");
            for (col=0; col<cols; ++col) {
                letterIndex = col + row*cols;
                if (letterIndex < me.letters.length) {
                    letter = me.letters[letterIndex];
                    cellHtml = $("<div>");
                    cellHtml.addClass("letterResponseCell");
                    cellHtml.addClass("ospanButton");
                    cellHtml.html(letter);
                    rowHtml.append(cellHtml);
                }
            }
            letterTable.append(rowHtml);
        }
        
        me.el.letterRecallList = me.el.letterResp.find(".letterResponseDisplay");
        me.el.recallExit       = me.el.letterResp.find(".exitButton");
        me.el.recallTable      = letterTable;
        me.el.blankResponse    = me.el.letterResp.find(".blankResponse");
        
        letterTable.on("click", ".letterResponseCell", function(e) {
            me.registerLetterResponse(this.innerHTML);
        });
        
        me.el.blankResponse.on("click", function() {
            if ($(this).hasClass("disabled")) return;
            me.registerLetterResponse("_");
        });
        
        me.el.letterResp.find(".clearButton").on("click", function() {
            if ($(this).hasClass("disabled")) return;
            me.resetRecall();
        });
        
        me.el.letterResp.find(".exitButton").on("click", function() {
            if ($(this).hasClass("disabled")) return;
            me.endRecall();
        });
    },
    
    prepareMath: function() {
        var me = this;
        
        me.el.mathResp.find(".ospanButton").on("click", function() {
            me.makeMathResponse(this.innerHTML);
        });
    },
    
    registerLetterResponse: function(selection) {
        var me = this;
        
        var spanLength = me.currentSpanGroup[me.currentSpan].length;
        var respLength = me.el.letterRecallList.html().length;
        
        if (respLength === spanLength) {
            return false;
        } else if (respLength === spanLength - 1) {
            me.el.recallTable.find(".ospanButton").addClass("disabled");
            me.el.blankResponse.addClass("disabled");
            me.el.recallExit.removeClass("disabled");
        }
        
        me.el.letterRecallList.append(selection);
    },
    
    resetRecall: function() {
        var me = this;
        
        me.el.letterRecallList.html("");
        
        me.el.letterResp.find(".disabled").removeClass("disabled");
        me.el.recallExit.addClass("disabled");
    },
    
    formatMathProblem: function(math) {
        return "(" + math[0] + " " + math[1] + " " + math[2] + ") " + math[3] + " " + math[4] + " = ?";
    },
    
    getCorrectAnswer: function(math) {
        var ans;
        
        if (math[1] === '/') {
            ans = math[0] / math[2];
        } else {
            ans = math[0] * math[2];
        }
        
        if (math[3] === '-') {
            ans -= math[4];
        } else {
            ans += math[4];
        }
        
        if (ans === math[5]) {
            return "True";
        } else {
            return "False";
        }
    },
    
    respondToClick: function() {
        var me = this;
        
        /* * * * using buttons now * * *
        if (me.currentPhase === "InitialInstructions"
            || me.currentPhase === "practiceMathInstructions"
            || me.currentPhase === "practiceBothInstructions"
            || me.currentPhase === "finalInstructions"
        ) {
            me.advanceInstructions();
        }
        */
        
        if (me.showingMath) {
            me.endMathProblem(false);
        }
        
        if (me.currentPhase === "pracMathFeedback") {
            me.endPracticeMathFeedback();
        }
    },
    
    advanceInstructions: function() {
        var me = this;
        
        var curPage = $(".instructions:visible").hide(100);
        var nextPage = curPage.next();
        
        if (nextPage.length > 0) {
            nextPage.delay(200).show(100);
        } else {
            me.endInstructionsPhase();
        }
    },
    
    backInstructions: function() {
        var me = this;
        
        var curPage = $(".instructions:visible");
        var prevPage = curPage.prev();
        
        if (prevPage.length > 0) {
            curPage.hide(100);
            prevPage.delay(200).show(100);
        }
    },
    
    endInstructionsPhase: function() {
        var me = this;
        
        if (me.currentPhase === "InitialInstructions") me.endInitialInstructionsPhase();
        if (me.currentPhase === "practiceMathInstructions") me.endPracticeMathInstructions();
        if (me.currentPhase === "practiceBothInstructions") me.endPracticeBothInstructions();
        if (me.currentPhase === "finalInstructions") me.endFinalInstructions();
    },
    
    
    /* * * * * * * * * * * * * * * * * * * * * * *
     * Functions for transitioning between phases
     */
    init: function() {
        var me = this;
        
        me.el.ospan = $("#ospanContainer");
        
        me.el.initInstr  = me.el.ospan.find(".initialInstructionsContainer");
        me.el.mathInstr  = me.el.ospan.find(".mathInstructionsContainer");
        me.el.bothInstr  = me.el.ospan.find(".bothInstructionsContainer");
        me.el.finalInstr = me.el.ospan.find(".finalInstructionsContainer");
        me.el.mathDisp   = me.el.ospan.find(".mathDisplayContainer");
        me.el.mathProb   = me.el.ospan.find(".mathProblem");
        me.el.mathResp   = me.el.ospan.find(".mathRespondContainer");
        me.el.solution   = me.el.ospan.find(".solution");
        me.el.mathFeed   = me.el.ospan.find(".mathFeedback");
        me.el.letterDisp = me.el.ospan.find(".letterDisplayContainer");
        me.el.letterResp = me.el.ospan.find(".letterRespondContainer");
        me.el.spanFeed   = me.el.ospan.find(".spanFeedbackContainer");
        me.el.spanMath   = me.el.ospan.find(".spanMathFeedback");
        me.el.spanMathP  = me.el.ospan.find(".spanMathPercentage");
        me.el.pracMathF  = me.el.ospan.find(".practiceMathFeedback");
            
        me.createStimuli();
        
        me.prepareInstructions();
        me.prepareRecall();
        me.prepareMath();
        
        $("body").on("click", function() {
            me.respondToClick();
        });
        
        me.beginInitialInstructions();
    },
    
    beginInitialInstructions: function() {
        var me = this;
        
        me.currentPhase = "InitialInstructions";
        
        me.el.initInstr.show().find(".instructions:first").show();
    },
    
    endInitialInstructionsPhase: function() {
        var me = this;
        
        me.el.initInstr.hide();
        me.currentPhase = null;
        
        me.beginPracticeLetterTask();
    },
    
    beginPracticeLetterTask: function() {
        var me = this;
        
        me.currentPhase = "practiceLetters";
        me.phaseTimes[me.currentPhase] = Date.now();
        
        me.currentSpanGroup = me.Stimuli.practiceLetters;
        me.currentSpan = 0;
        
        me.startCurrentSpan();
    },
    
    endPracticeLetters: function() {
        var me = this;
        
        me.phaseTimes[me.currentPhase] = Date.now() - me.phaseTimes[me.currentPhase];
        me.currentPhase = null;
        
        COLLECTOR.timer(0.5, function() {
            me.startPracticeMath();
        });
    },
    
    startPracticeMath: function() {
        var me = this;
        
        me.currentPhase = "practiceMathInstructions";
        me.giveMathFeedback = true;
        me.el.mathInstr.show().find(".instructions:first").show();
    },
    
    endPracticeMathInstructions: function() {
        var me = this;
        
        me.currentPhase = "practiceMath";
        me.phaseTimes[me.currentPhase] = Date.now();
        
        me.currentSpanGroup = me.Stimuli.practiceMath;
        me.currentSpan = 0;
        
        me.startCurrentSpan();
    },
    
    endPracticeMath: function() {
        var me = this;
        
        me.phaseTimes[me.currentPhase] = Date.now() - me.phaseTimes[me.currentPhase];
        me.currentPhase = null;
        me.giveMathFeedback = false;
        
        // calculate math time limit
        var lastSpan = me.resp[me.resp.length-1];
        var problemDurations = lastSpan.mathProbDur.split(",");
        var i, len;
        var sum = 0;
        
        for (i=0, len=problemDurations.length; i<len; ++i) {
            sum += parseInt(problemDurations[i]);
        }
        
        var ave = sum / len;
        
        var stdev = 0;
        
        for (i=0; i<len; ++i) {
            stdev += Math.pow(problemDurations[i] - ave, 2);
        }
        
        stdev /= (len-1);
        
        stdev = Math.sqrt(stdev);
        
        me.mathTimeLimit = (ave + (2.5 * stdev));
        
        me.startPracticeMathFeedback();
    },
    
    startPracticeMathFeedback: function() {
        var me = this;
        
        me.currentPhase = "pracMathFeedback";
        
        // check scores
        var lastSpan = me.resp[me.resp.length-1];
        var mathCorrect = lastSpan.mathCorrect.split(",");
        var correct = 0;
        for (var i=0, len=mathCorrect.length; i<len; ++i) {
            if (mathCorrect[i] === "1") ++correct;
        }
        var percent = Math.round(100*(correct/len));
        
        me.el.pracMathF.html("<p>You were correct on "+correct+" of "+len+" math trials. That is "+percent+"% correct.</p><br><p>Click the mouse to continue.</p>").show();
    },
    
    endPracticeMathFeedback: function() {
        var me = this;
        
        me.currentPhase = null;
        me.el.pracMathF.hide();
        
        COLLECTOR.timer(0.5, function() {
            me.startCombinedPractice();
        });
    },
    
    startCombinedPractice: function() {
        var me = this;
        
        me.currentPhase = "practiceBothInstructions";
        me.el.bothInstr.show().find(".instructions:first").show();
    },
    
    endPracticeBothInstructions: function() {
        var me = this;
        
        me.currentPhase = "practiceBoth";
        me.phaseTimes[me.currentPhase] = Date.now();
        
        me.currentSpanGroup = me.Stimuli.practiceBoth;
        me.currentSpan = 0;
        
        me.mathTotal.count = 0;
        me.mathTotal.correct = 0;
        
        me.startCurrentSpan();
    },
    
    endPracticeBoth: function() {
        var me = this;
        
        me.phaseTimes[me.currentPhase] = Date.now() - me.phaseTimes[me.currentPhase];
        me.currentPhase = null;
        
        COLLECTOR.timer(0.5, function() {
            me.startRealTask();
        });
    },
    
    startRealTask: function() {
        var me = this;
        
        me.currentPhase = 'finalInstructions';
        me.el.finalInstr.show().find(".instructions:first").show();
    },
    
    endFinalInstructions: function() {
        var me = this;
        
        me.currentPhase = "realTask";
        me.phaseTimes[me.currentPhase] = Date.now();
        
        me.currentSpanGroup = me.Stimuli.realSpans;
        me.currentSpan = 0;
        
        me.mathTotal.count = 0;
        me.mathTotal.correct = 0;
        
        me.startCurrentSpan();
    },
    
    endRealTask: function() {
        var me = this;
        
        me.phaseTimes[me.currentPhase] = Date.now() - me.phaseTimes[me.currentPhase];
        me.currentPhase = null;
        
        me.endOspan();
    },
    
    endOspan: function() {
        var me = this;
        
        var output = {}, submitHtml = '<div style="display: none;">';
        
        for (var prop in me.resp[0]) {
            output[prop] = [];
        }
        
        for (var i=0, len=me.resp.length; i<len; ++i) {
            for (prop in me.resp[i]) {
                output[prop].push(me.resp[i][prop]);
            }
        }
        
        for (prop in output) {
            submitHtml += "<input name='ospan_"+prop+"' value='"+output[prop].join("|")+"'>";
        }
        
        var mathPercent = Math.round(100*(me.mathTotal.correct / me.mathTotal.count));
        var goodEnoughMath;
        if (mathPercent < me.Settings.mathPercentageRequired) {
            goodEnoughMath = 0;
        } else {
            goodEnoughMath = 1;
        }
        submitHtml += "<input name='ospan_MathOverallPercent' value='"+mathPercent+"'>";
        submitHtml += "<input name='ospan_Valid' value='"+goodEnoughMath+"'>";
        
        submitHtml += "<input name='ospan_MathTimeLimit' value='"+me.mathTimeLimit+"'>";
        
        for (prop in me.phaseTimes) {
            submitHtml += "<input name='ospan_phaseTime-"+prop+"' value='"+me.phaseTimes[prop]+"'>";
        }
        
        var OspanScore = 0, TotalNumberCorrect = 0, MathErrors = 0, SpeedErrors = 0, AccuracyErrors = 0;
        
        var j, spanLength;
        var resp, lettersCorrect, mathCorrect, mathAnswers;
        var allLettersCorrect;
        for (var i=0, len=me.resp.length; i<len; ++i) {
            if (me.resp[i].phase !== "realTask") continue;
            
            resp = me.resp[i];
            spanLength = resp.length;
            
            lettersCorrect = resp.letterCorrect.split(",");
            mathCorrect    = resp.mathCorrect.split(",");
            mathAnswers    = resp.mathAnswers.split(",");
            
            // calculate scores based on letters
            allLettersCorrect = true;
            for (j=0; j<spanLength; ++j) {
                if (lettersCorrect[j] === "0") {
                    allLettersCorrect = false;
                } else {
                    ++TotalNumberCorrect;
                }
            }
            if (allLettersCorrect) OspanScore += spanLength;
            
            // calculate math scores
            for (j=0; j<spanLength; ++j) {
                if (mathCorrect[j] === "0") {
                    ++MathErrors;
                    if (mathAnswers[j] === "") {
                        ++SpeedErrors;
                    } else {
                        ++AccuracyErrors;
                    }
                }
            }
        }
        
        submitHtml += "<input name='ospan_OspanScore'         value='"+ OspanScore         +"'>";
        submitHtml += "<input name='ospan_TotalNumberCorrect' value='"+ TotalNumberCorrect +"'>";
        submitHtml += "<input name='ospan_MathErrors'         value='"+ MathErrors         +"'>";
        submitHtml += "<input name='ospan_SpeedErrors'        value='"+ SpeedErrors        +"'>";
        submitHtml += "<input name='ospan_AccuracyErrors'     value='"+ AccuracyErrors     +"'>";
        
        submitHtml += "</div>";
        $("form").append(submitHtml).submit();
    },
    
    
    
    /* * * * * * * * * * * *
     * The following functions are all 
     * used to cycle through a span
     */
    startCurrentSpan: function() {
        var me = this;
        
        var csd = me.currentSpanData;
        
        csd.recallDur = null;
        csd.recallResp = null;
        csd.timestamp = null;
        
        csd.math = [];
        
        var curSpan = me.currentSpanGroup[me.currentSpan];
        
        if (curSpan[0].math !== null) {
            for (var i=0, len=curSpan.length; i<len; ++i) {
                csd.math.push({probDur: null, solveDur: null, answer: null});
            }
        }
        
        me.currentTrial = 0;
        
        me.startCurrentTrial();
    },
    
    startCurrentTrial: function() {
        var me = this;
        
        if (me.currentSpanGroup[me.currentSpan][me.currentTrial].math === null) {
            me.startLetter();
        } else {
            me.startMath();
        }
    },
    
    startMath: function() {
        var me = this;
        
        COLLECTOR.timer(me.Settings.time.preMathProblem, function() {
            var math = me.currentSpanGroup[me.currentSpan][me.currentTrial].math;
            var mathHtml = me.formatMathProblem(math);
            me.el.mathProb.html(mathHtml);
            me.el.mathDisp.show();
            
            me.showingMath = true;
            me.currentSpanData.timestamp = Date.now();
            
            if (me.mathTimeLimit !== null) {
                COLLECTOR.timer(me.mathTimeLimit/1000, function() {
                    if (me.showingMath
                        // potentially, one could have some enormous time limit, and actually be in the midst
                        // of the next math problem when this timer expires. So, im checking if not only
                        // are we in the math solving stage, but also are we currently within 2000 ms of
                        // the math timestamp + time limit
                        // if we have looped around, we should be much further off than just 300 ms
                        && Math.abs(Date.now() - (me.currentSpanData.timestamp + me.mathTimeLimit)) < 2000
                    ) {
                        me.endMathProblem(true);
                    }
                });
            }
        });
    },
    
    endMathProblem: function(timeout) {
        var me = this;
        
        me.showingMath = false;
        
        var probDur = Date.now() - me.currentSpanData.timestamp;
        me.currentSpanData.math[me.currentTrial].probDur = probDur;
        
        me.el.mathDisp.hide();
        
        if (timeout) {
            me.endMathSolve();
        } else {
            me.startMathSolve();
        }
    },
    
    startMathSolve: function() {
        var me = this;
        
        COLLECTOR.timer(me.Settings.time.preMathSolution, function() {
            var solution = me.currentSpanGroup[me.currentSpan][me.currentTrial].math[5];
            
            me.el.solution.html(solution);
            me.el.mathFeed.html("&nbsp;");
            me.el.mathResp.show();
            me.allowMathResponse = true;
            me.currentSpanData.timestamp = Date.now();
        });
    },
    
    makeMathResponse: function(response) {
        var me = this;
        
        if (!me.allowMathResponse) return;
        me.allowMathResponse = false;
        
        me.currentSpanData.math[me.currentTrial].solveDur = Date.now() - me.currentSpanData.timestamp;
        me.currentSpanData.math[me.currentTrial].answer = response;
        
        if (me.giveMathFeedback) {
            var math = me.currentSpanGroup[me.currentSpan][me.currentTrial].math;
            var correctAns = me.getCorrectAnswer(math);
            
            if (response === correctAns) {
                me.el.mathFeed.html("Correct");
            } else {
                me.el.mathFeed.html("Incorrect");
            }
            
            COLLECTOR.timer(me.Settings.time.mathFeedback, function() {
                me.endMathSolve();
            });
        } else {
            me.endMathSolve();
        }
    },
    
    endMathSolve: function() {
        var me = this;
        
        me.el.mathResp.hide();
        
        if (me.currentSpanGroup[me.currentSpan][me.currentTrial].letter === null) {
            me.advanceTrial();
        } else {
            me.startLetter();
        }
    },
    
    startLetter: function() {
        var me = this;
        
        COLLECTOR.timer(me.Settings.time.preLetter, function() {
            var letter = me.currentSpanGroup[me.currentSpan][me.currentTrial].letter;
            
            me.el.letterDisp.html(letter).show();
            
            COLLECTOR.timer(me.Settings.time.letter, function() {
                me.endLetter();
            });
        });
    },
    
    endLetter: function() {
        var me = this;
        
        me.el.letterDisp.hide();
        
        me.advanceTrial();
    },
    
    advanceTrial: function() {
        var me = this;
        
        ++me.currentTrial;
        
        if (me.currentSpanGroup[me.currentSpan].length <= me.currentTrial) {
            if (me.currentSpanGroup[me.currentSpan][0].letter === null) {
                me.endCurrentSpan();
            } else {
                me.startRecallTest();
            }
        } else {
            me.startCurrentTrial();
        }
    },
    
    startRecallTest: function() {
        var me = this;
        
        COLLECTOR.timer(me.Settings.time.preRecall, function() {
            me.resetRecall();
            me.el.letterResp.show();
            me.currentSpanData.timestamp = Date.now();
        });
    },
    
    endRecall: function() {
        var me = this;
        
        me.el.letterResp.hide();
        
        me.currentSpanData.recallDur = Date.now() - me.currentSpanData.timestamp;
        me.currentSpanData.recallResp = me.el.letterRecallList.html().split("");
        
        me.startSpanFeedback();
    },
    
    startSpanFeedback: function() {
        var me = this;
        
        COLLECTOR.timer(me.Settings.time.preFeedback, function() {
            var i, len = me.currentSpanGroup[me.currentSpan].length;
            var correctLetters = 0;
            for (i=0; i<len; ++i) {
                if (me.currentSpanData.recallResp[i] === me.currentSpanGroup[me.currentSpan][i].letter) {
                    ++correctLetters;
                }
            }
            
            me.currentSpanData.correctLetters = correctLetters;
            me.el.spanFeed.find(".letterRecall").html(correctLetters);
            me.el.spanFeed.find(".spanLength").html(len);
            
            if (me.currentSpanGroup[me.currentSpan][0].math === null) {
                me.el.spanMath.hide();
                me.el.spanMathP.hide();
            } else {
                var math, correctAns, mathResp, mathErrors = 0;
                for (i=0; i<len; ++i) {
                    math = me.currentSpanGroup[me.currentSpan][i].math;
                    correctAns = me.getCorrectAnswer(math);
                    mathResp = me.currentSpanData.math[i].answer;
                    if (mathResp !== correctAns) ++mathErrors;
                }
                var mathFeedback = "<p>You made "+mathErrors+" math error(s) on this set of trials.</p>";
                if (mathErrors >= 3) {
                    mathFeedback += "<p>You have made a total of 3 or more math errors during this set of trials. Please do your best on the math.</p>";
                }
                
                me.el.spanMath.html(mathFeedback).show();
        
                me.mathTotal.count += len;
                me.mathTotal.correct += (len-mathErrors);
                
                var mathAve = Math.round(100*(me.mathTotal.correct/me.mathTotal.count));
                if (mathAve < me.Settings.mathPercentageRequired) {
                    me.el.spanMathP.addClass("warning");
                } else {
                    me.el.spanMathP.removeClass("warning");
                }
                me.el.spanMathP.html("Overall math accuracy: " + mathAve + "%").show();
            }
            
            me.el.spanFeed.show();
            
            COLLECTOR.timer(me.Settings.time.feedback, function() {
                me.el.spanFeed.hide();
                me.endCurrentSpan();
            });
        });
    },
    
    endCurrentSpan: function() {
        var me = this;
        
        me.recordCurrentSpanData();
        
        ++me.currentSpan;
        
        if (me.currentSpanGroup.length <= me.currentSpan) {
            me.endCurrentSpanGroup();
        } else {
            me.startCurrentSpan();
        }
    },
    
    recordCurrentSpanData: function() {
        var me = this;
        
        var curSpan = me.currentSpanGroup[me.currentSpan];
        var curSpanNumber = me.currentSpan+1;
        var curSpanLen = curSpan.length;
        var i;
        
        var spanData = {};
        
        spanData.phase = me.currentPhase;
        spanData.SpanNumber = me.currentSpan;
        spanData.length = curSpanLen;
        spanData.recallDuration = me.currentSpanData.recallDur;
        
        var correctAns;
        var letters        = [],
            letterResponse = [],
            letterCorrect  = [],
            mathProblems   = [],
            mathSolutions  = [],
            mathAnswers    = [],
            mathProbDur    = [],
            mathSolveDur   = [],
            mathCorrectAns = [],
            mathCorrect    = [];
            
        for (i=0; i<curSpanLen; ++i) {
            if (curSpan[i].letter !== null) {
                letters.push(curSpan[i].letter);
                letterResponse.push(me.currentSpanData.recallResp[i]);
                if (me.currentSpanData.recallResp[i] === curSpan[i].letter) {
                    letterCorrect.push(1);
                } else {
                    letterCorrect.push(0);
                }
            }
            
            if (curSpan[i].math !== null) {
                mathProblems.push(me.formatMathProblem(curSpan[i].math));
                mathSolutions.push(curSpan[i].math[5]);
                mathProbDur.push(me.currentSpanData.math[i].probDur);
                mathSolveDur.push(me.currentSpanData.math[i].solveDur);
                mathAnswers.push(me.currentSpanData.math[i].answer);
                correctAns = me.getCorrectAnswer(curSpan[i].math);
                mathCorrectAns.push(correctAns);
                if (me.currentSpanData.math[i].answer === correctAns) {
                    mathCorrect.push(1);
                } else {
                    mathCorrect.push(0);
                }
            }
        }
        
        spanData.letters        = letters.join(",");
        spanData.letterResponse = letterResponse.join(",");
        spanData.letterCorrect  = letterCorrect.join(",");
        spanData.mathProblems   = mathProblems.join(",");
        spanData.mathSolutions  = mathSolutions.join(",");
        spanData.mathAnswers    = mathAnswers.join(",");
        spanData.mathProbDur    = mathProbDur.join(",");
        spanData.mathSolveDur   = mathSolveDur.join(",");
        spanData.mathCorrectAns = mathCorrectAns.join(",");
        spanData.mathCorrect    = mathCorrect.join(",");
        
        me.resp.push(spanData);
    },
    
    endCurrentSpanGroup: function() {
        var me = this;
        
        if (me.currentPhase === "practiceLetters") me.endPracticeLetters();
        if (me.currentPhase === "practiceMath") me.endPracticeMath();
        if (me.currentPhase === "practiceBoth") me.endPracticeBoth();
        if (me.currentPhase === "realTask") me.endRealTask();
    },
}

$(window).load(function() {
    OSpan.init();
});

// from Mike Bostock at https://bost.ocks.org/mike/shuffle/
function shuffle(array) {
  var m = array.length, t, i;

  // While there remain elements to shuffle…
  while (m) {

    // Pick a remaining element…
    i = Math.floor(Math.random() * m--);

    // And swap it with the current element.
    t = array[m];
    array[m] = array[i];
    array[i] = t;
  }

  return array;
}
</script>

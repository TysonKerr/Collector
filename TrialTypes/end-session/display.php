<?php
    $max_time = get_max_time_for_waiting($settings['max_time']);
?>
<style>
    .end-session-message {
        display: inline-block;
        max-width: 700px;
        margin: auto;
        text-align: left;
    }
</style>

<div class="end-session-message">
    <p><?= $text !== '' ? $text : "That's it for this session." ?></p>
    
    <p><?php
        if (is_numeric($settings['max_time'])) {
            echo get_wait_time_message($max_time);
        }
    ?></p>
</div>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

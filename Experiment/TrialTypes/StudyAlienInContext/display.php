<?php

require __DIR__ . '/../aliens.php';
require __DIR__ . '/definitions.php';
require __DIR__ . '/custom_style.php';

if (!isset($_SESSION['Study_Misses'])) $_SESSION['Study_Misses'] = 0;

$settings_parsed = parse_settings($settings);
/* note, below the features are being adjusted by -1 to keep them compatible with the old format, where values started at 0 rather than 1 */

?>

<script>
"use strict";

let prev_misses = <?= $_SESSION['Study_Misses'] ?>;
</script>

<div class="context_container">
    <?= show("Images/Contexts/$context") ?>
    <div class="canvas_container"><div><div>
        <?= create_alien([$body-1, $arms-1, $tail-1, $legs-1, $antenna-1, $mouth-1, $eyes-1]) ?>
    </div></div></div>
</div>

<div class="feedback">
    You missed the alien! Please respond more quickly and accurately next time!
    <p class="warning">If you continue to miss trials, the experiment will end early.</p>
</div>

<div class="hidden">
    <input type="hidden" name="alien_delay">
    <input type="hidden" name="alien_side">
    <input type="hidden" name="alien_rt" value="no response">
    <input type="hidden" name="alien_response">
    <input type="hidden" name="alien_response_correct" value="0">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<?php

require __DIR__ . '/custom_script.php';

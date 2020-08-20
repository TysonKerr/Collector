<?php

require ROOT . '/PHP/definitions/done.php';

check_if_logged_in();
check_my_eligibility();
check_if_has_more_experiment_to_do();
record_ending_reached();

if (has_next_experiment()) {
    redirect_to_next_experiment();
}

?>
<p>You have finished the task!</p>

<p>Your verification code is:</p>
<p><b><?= get_verification_code() ?></b></p>

<form method="post">
    <button type="submit" name="collector_logout" value="logout" class="logout">Log out</button>
</form>

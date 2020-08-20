<?php
    if ($page_settings['wrong pass']) {
        echo '<div class="error">Incorrect password</div>';
    }
?>

<p>Please enter the password:</p>

<form method="post" autocomplete="off">
    <input type="password" name="s" autofocus>
    <button type="submit">Submit</button>
</form>

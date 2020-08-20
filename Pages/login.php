<?php
    require ROOT . '/PHP/definitions/login.php';
    $error_message = check_for_submitted_login_data();
    check_all_conditions();
    $conditions = get_conditions();
?>
<form id="content" name="Login" method="get" autocomplete="off" class="login main-form">
    <h1 class="textcenter"><?= get_config('welcome') ?></h1>
    <div class="exp-description"><?= get_config('exp_description') ?></div>
    
    <section id="indexLogin" class="flexVert">
        <div class="login-error-message"><?= $error_message ?></div>
        <div class="textcenter flexChild">
            Please enter your <?= get_config('ask_for_login') ?>
        </div>
        <div class="flexChild">
            <input name="u" type="text" value="" autocomplete="off" class="collectorInput" placeholder="<?= get_config('ask_for_login') ?>">
            <input name="c" value="<?= get_input_conditions_index() ?>" type="hidden">
            
            <!-- Condition selector -->
            <select name="sc" class="<?= get_condition_select_class() ?>">
                <option default selected value="-1">Auto</option>
                <?= get_conditions_as_options($conditions) ?>
            </select>
            <button class="collectorButton" type="submit">Login</button>
        </div>
    </section>
</form>

<embed src="<?= get_consent_path() ?>" class="login-consent-form">

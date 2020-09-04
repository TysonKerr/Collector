<p>I'm sorry, but this experiment cannot continue, and will end now.</p>

<?php if (get_config('show_verification')): ?>
<p>If you are participating for online payment or credit, you may still be
    eligible for partial compensation, so please use the following verification
    code:</p>

<p><strong>EE-<?= $_SESSION['ID'] ?></strong></p>
<?php endif; ?>

<p>
    You did not comply with the task instructions and thus cannot continue with
    the experiment.
</p>

<p>
    If you believe you encountered this page due to an error, please contact the
    experimenter and someone will examine the data and get back to you.
</p>

<?php if ($_CONFIG->mTurk_mode): ?>
<p>
    To complete the HIT and receive partial payment, please submit the verification
    code below:
</p>

</p>
    g9wfs0dl-<?= $_SESSION['ID'] ?>
</p>
<?php endif; ?>

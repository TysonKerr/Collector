<?php
    require __DIR__ . '/init.php';
    start_main_output_buffer();
?>
<h2>Welcome to the Collector</h2>

<p>To begin one of the experiments, please click on a link below:</p>

<div>
<?php
    foreach (get_list_of_experiments() as $exp_name): ?>
    <div><a href="<?= $exp_name ?>/login/"><?= $exp_name ?></a></div>
    <?php endforeach;
?>
</div>

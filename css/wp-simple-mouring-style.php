<?php
header("Content-type: text/css; charset: UTF-8");

$grayscale = get_settings_simple_mouring()['grayscale'];
?>
<style>
    html{
        -webkit-filter: grayscale(<?= $grayscale . '%' ?>);
        -moz-filter: grayscale(<?= $grayscale . '%' ?>);
        filter: grayscale(<?= $grayscale . '%' ?>);
    }
</style>
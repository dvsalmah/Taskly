<?php
$_pageName = $pageTitle ?? '';
$_title    = $_pageName !== '' ? $_pageName . ' | Taskly' : 'Taskly';
$_depth    = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../' : '';
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="<?= $_depth ?>assets/icon.svg">
    <title><?= htmlspecialchars($_title) ?></title>

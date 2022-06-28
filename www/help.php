<?php

require_once 'includes/config.php';

if (!$user['admin']) {
    $_SESSION['message']['type'] = 'danger';
    $_SESSION['message']['text'] = 'You are not authorized to view this page';
    header('Location: ' . $_SERVER['referrer']);
    exit;
}
require_once 'includes/views/start.php';
require_once 'includes/views/help.php';
require_once 'includes/views/end.php';

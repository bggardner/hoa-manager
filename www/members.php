<?php

require_once 'includes/config.php';

try {
    if (isset($_GET['delete'])) {
        if (!$user['admin']) {
            throw new Exception('You are not authorized to delete members.');
        }
        $admin_count = HOA\Service::executeStatement('SELECT COUNT(*) FROM `' . HOA\Settings::get('table_prefix') . 'members` WHERE `admin` = 1 AND `id` != ?', [
            ['value' => $_GET['delete'], 'type' => \PDO::PARAM_INT]
        ])->fetchColumn();
        if (!$admin_count) {
            throw new Exception('Cannot delete last administrator');
        }
        HOA\Service::executeStatement('DELETE FROM `' . HOA\Settings::get('table_prefix') . 'members` WHERE `id` = ?', [
            ['value' => $_GET['delete'], 'type' => \PDO::PARAM_INT]
        ]);
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Member deleted successfully';
        header('Location: ' . (basename($_SESSION['referrer']) == 'profile.php?id=' . $_GET['delete'] ? dirname($_SERVER['PHP_SELF']) . '/members.php' : $_SESSION['referrer']));
        exit;
    } else if ($_POST) {
        if (!$user['admin']) {
            throw new Exception('You are not authorized to add members.');
        }
        $id = HOA\Service::insertStatement('INSERT INTO `' . HOA\Settings::get('table_prefix') . 'members` SET `parcel` = ?, `email` = ?, `data` = "{}"', [
            ['value' => $_POST['parcel'], 'type' => \PDO::PARAM_INT],
            ['value' => $_POST['email'], 'type' => \PDO::PARAM_STR]
        ]);
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Member added successfully';
        HOA\Settings::get('on_member_add')($_POST['email']);
        header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/profile.php?id=' . $id);
        exit;
    }
} catch (Exception $e) {
    $_SESSION['message']['type'] = 'danger';
    $_SESSION['message']['text'] = $e->getMessage();
}


require_once 'includes/views/start.php';
if ($user['admin']) {
    require_once 'includes/views/members.php';
} else {
    echo '<div class="alert alert-danger" role="alert">You are not authorized to view this page.</div>';
}
require_once 'includes/views/end.php';

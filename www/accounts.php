<?php

require_once 'includes/config.php';

if ($_POST) {
    try {
        if (!$user['admin']) {
            throw new Exception('You are not authorized to edit accounts.');
        }
        if (isset($_POST['id'])) {
            if (isset($_POST['delete'])) { // Delete
                HOA\Service::executeStatement('DELETE FROM `' . HOA\Settings::get('table_prefix') . 'accounts` WHERE `id` = ?', [
                    ['value' => $_POST['id'], 'type' => \PDO::PARAM_INT]
                ]);
                $_SESSION['message']['type'] = 'success';
                $_SESSION['message']['text'] = 'Account deleted successfully';
            } else { // Edit
                HOA\Service::executeStatement('UPDATE `' . HOA\Settings::get('table_prefix') . 'accounts` SET `name` = ?  WHERE `id` = ?', [
                    ['value' => $_POST['name'], 'type' => \PDO::PARAM_STR],
                    ['value' => $_POST['id'], 'type' => \PDO::PARAM_INT]
                ]);
                $_SESSION['message']['type'] = 'success';
                $_SESSION['message']['text'] = 'Account updated successfully';
            }
        } else { // Add
            HOA\Service::executeStatement('INSERT INTO `' . HOA\Settings::get('table_prefix') . 'accounts` (`name`) VALUES (?)', [
                ['value' => $_POST['name'], 'type' => \PDO::PARAM_STR]
            ]);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Account added successfully';
        }
    } catch (Exception $e) {
        $_SESSION['message']['type'] = 'danger';
        $_SESSION['message']['text'] = $e->getMessage();
    }
}

header('Location: ' . $_SESSION['referrer']);
exit;

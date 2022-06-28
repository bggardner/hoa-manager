<?php

require_once 'includes/config.php';

if ($_POST) {
    try {
        if (!$user['admin']) {
            throw new Exception('You are not authorized to edit the ledger');
        }
        if (isset($_POST['delete'])) { // Delete Entry
            HOA\Service::executeStatement('DELETE FROM `' . HOA\Settings::get('table_prefix') . 'ledger` WHERE `id` = ?', [
                ['value' => $_POST['id'], 'type' => \PDO::PARAM_INT]
            ]);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Entry deleted successfully.';
        } else if (isset($_POST['edit'])) { // Edit Entry
            HOA\Service::executeStatement('UPDATE `' . HOA\Settings::get('table_prefix') . 'ledger` SET `amount` = ? WHERE `id` = ?', [
                ['value' => $_POST['amount'], 'type' => \PDO::PARAM_STR],
                ['value' => $_POST['id'], 'type' => \PDO::PARAM_INT]
            ]);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Entry updated successfully.';
        } else { // Add entry
            HOA\Service::executeStatement('INSERT INTO `' . HOA\Settings::get('table_prefix') . 'ledger` (`account`, `date`, `budget`, `category`, `party`, `amount`) VALUES (?, ?, ?, ?, ?, ?)', [
                ['value' => $_POST['account'], 'type' => \PDO::PARAM_INT],
                ['value' => $_POST['date'], 'type' => \PDO::PARAM_STR],
                ['value' => $_POST['budget'] ?: null, 'type' => $_POST['budget'] ? \PDO::PARAM_INT : \PDO::PARAM_NULL],
                ['value' => $_POST['category'], 'type' => \PDO::PARAM_INT],
                ['value' => $_POST['party'], 'type' => \PDO::PARAM_STR],
                ['value' => $_POST['amount'], 'type' => \PDO::PARAM_STR]
             ]);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Entry added successfully';
        }
        header('Location: ' . $_SESSION['referrer']);
        exit;
    } catch (Exception $e) {
        $_SESSION['message']['type'] = 'danger';
        $_SESSION['message']['text'] = $e->getMessage();
    }
}

require_once 'includes/views/start.php';
if ($user['admin']) {
    require_once 'includes/views/ledger.php';
} else {
    echo '<div class="alert alert-danger" role="alert">You are not authorized to view this page.</div>';
}
require_once 'includes/views/end.php';

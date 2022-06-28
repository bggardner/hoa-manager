<?php

require_once 'includes/config.php';

if ($_POST) {
    try {
        if (!$user['admin']) {
            throw new Exception('You are not authorized to edit receivables');
        }
        if (isset($_POST['batch'])) {
            if (!isset($_POST['ids']) || !count($_POST['ids'])) {
                throw new Exception('No entries selected for batch processing');
            }
            switch ($_POST['batch']) {
                case 'delete':
                    HOA\Service::executeStatement(
                        'DELETE FROM `' . HOA\Settings::get('table_prefix') . 'receivables` WHERE `id` IN (' . implode(',', array_fill(0, count($_POST['ids']), '?')) . ')',
                        array_map(function($id) { return ['value' => $id, 'type' => \PDO::PARAM_INT]; }, $_POST['ids'])
                    );
                    $_SESSION['message']['type'] = 'success';
                    $_SESSION['message']['text'] = 'Entries deleted successfully.';
                    break;
                case 'statements':
                    $stmt = HOA\Service::executeStatement(
                        'SELECT 1 FROM `' . HOA\Settings::get('table_prefix') . 'receivables` WHERE `id` IN (' . implode(',', array_fill(0, count($_POST['ids']), '?')) . ')',
                        array_map(function($id) { return ['value' => $id, 'type' => \PDO::PARAM_INT]; }, $_POST['ids'])
                    );
                    break;
                default:
                    throw new Exception('Unknown batch command: ' . $_POST['batch']);
            }
        } else if (isset($_POST['delete'])) { // Delete Entry
            HOA\Service::executeStatement('DELETE FROM `' . HOA\Settings::get('table_prefix') . 'receivables` WHERE `id` = ?', [
                ['value' => $_POST['id'], 'type' => \PDO::PARAM_INT]
            ]);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Entry deleted successfully.';
        } else if (isset($_POST['edit'])) { // Edit Entry
            HOA\Service::executeStatement('UPDATE `' . HOA\Settings::get('table_prefix') . 'receivables` SET `amount` = ? WHERE `id` = ?', [
                ['value' => $_POST['amount'], 'type' => \PDO::PARAM_STR],
                ['value' => $_POST['id'], 'type' => \PDO::PARAM_INT]
            ]);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Entry updated successfully.';
        } else { // Add entry
            $params = [
                ['value' => $_POST['date'], 'type' => \PDO::PARAM_STR],
                ['value' => $_POST['description'], 'type' => \PDO::PARAM_STR],
                ['value' => $_POST['amount'], 'type' => \PDO::PARAM_STR]
            ];
            if ($_POST['parcel'] != '*') {
               $params[] = ['value' => $_POST['parcel'], 'type' => \PDO::PARAM_INT];
            }
            HOA\Service::executeStatement('
INSERT INTO `' . HOA\Settings::get('table_prefix') . 'receivables` (`parcel`, `date`, `description`, `amount`)
SELECT `parcels`.`id`, ?, ?, ?
FROM `parcels`
WHERE ' . ($_POST['parcel'] == '*' ? '`parcels`.`data`->>"$.house_number" != ""' : '`parcels`.`id` = ?') . '
            ', $params);
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
    require_once 'includes/views/receivables.php';
} else {
    echo '<div class="alert alert-danger" role="alert">You are not authorized to view this page.</div>';
}
require_once 'includes/views/end.php';

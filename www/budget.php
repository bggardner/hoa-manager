<?php

require_once 'includes/config.php';

if ($_POST) {
    try {
        if (!$user['admin']) {
            throw new Exception('You are not authorized to edit the budget');
        }
        if (isset($_POST['delete'])) { // Delete Entry
            HOA\Service::executeStatement('DELETE FROM `' . HOA\Settings::get('table_prefix') . 'budget` WHERE `year` = ? AND `category` = ?', [
                ['value' => $_POST['year'], 'type' => \PDO::PARAM_INT],
                ['value' => $_POST['category'], 'type' => \PDO::PARAM_INT]
            ]);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Entry deleted successfully.';
        } else if (isset($_POST['edit'])) { // Edit Entry
            HOA\Service::executeStatement('UPDATE `budget` SET `amount` = ? WHERE `year` = ? AND `category` = ?', [
                ['value' => $_POST['amount'], 'type' => \PDO::PARAM_STR],
                ['value' => $_POST['year'], 'type' => \PDO::PARAM_INT],
                ['value' => $_POST['category'], 'type' => \PDO::PARAM_INT]
            ]);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Entry updated successfully.';
        } else { // Add entry
            $has_nonzero_relatives = HOA\Service::executeStatement('
SELECT COUNT(*)
FROM `' . HOA\Settings::get('table_prefix') . 'budget`
WHERE
    `category` IN (
    SELECT `id`
    FROM `' . HOA\Settings::get('table_prefix') . 'accounting_categories`
    WHERE (
      `left` <= (SELECT `left` FROM `' . HOA\Settings::get('table_prefix') . 'accounting_categories` WHERE `id` = ?)
      AND `right` >= (SELECT `right` FROM `' . HOA\Settings::get('table_prefix') . 'accounting_categories` WHERE `id` = ?)
    ) OR (
      `left` >= (SELECT `left` FROM `' . HOA\Settings::get('table_prefix') . 'accounting_categories` WHERE `id` = ?)
      AND `right` <= (SELECT `right` FROM `' . HOA\Settings::get('table_prefix') . 'accounting_categories` WHERE `id` = ?)
    )
  )
  AND `year` = ?
            ', [
                ['value' => $_POST['category'], 'type' => \PDO::PARAM_INT],
                ['value' => $_POST['category'], 'type' => \PDO::PARAM_INT],
                ['value' => $_POST['category'], 'type' => \PDO::PARAM_INT],
                ['value' => $_POST['category'], 'type' => \PDO::PARAM_INT],
                ['value' => $_POST['year'], 'type' => \PDO::PARAM_INT]
            ])->fetchColumn();
            if ($has_nonzero_relatives) {
                throw new Exception('Category (self, ancestor, or descendant) already has an amount.');
            }
            HOA\Service::executeStatement('INSERT INTO `' . HOA\Settings::get('table_prefix') . 'budget` (`year`, `category`, `amount`) VALUES (?, ?, ?)', [
                ['value' => $_POST['year'], 'type' => \PDO::PARAM_INT],
                ['value' => $_POST['category'], 'type' => \PDO::PARAM_INT],
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
    require_once 'includes/views/budget.php';
} else {
    echo '<div class="alert alert-danger" role="alert">You are not authorized to view this page.</div>';
}
require_once 'includes/views/end.php';

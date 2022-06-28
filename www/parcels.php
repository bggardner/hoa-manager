<?php

require_once 'includes/config.php';

try {
    if ($_POST) {
        if (!$user['admin']) {
            throw new Exception('You are not authorized to update parcels');
        }
        if (isset($_POST['batch'])) {
            if (!isset($_POST['ids']) || !count($_POST['ids'])) {
                throw new Exception('No parcels were selected for batch processing');
            }
            switch ($_POST['batch']) {
                case 'delete':
                    HOA\Service::executeStatement(
                        'DELETE FROM `' . HOA\Settings::get('table_prefix') . 'parcels` WHERE `id` IN (' . implode(',', array_fill(0, count($_POST['ids']), '?')) . ')',
                        array_map(function($id) { return ['value' => $id, 'type' => \PDO::PARAM_INT]; }, $_POST['ids'])
                    );
                    $_SESSION['message']['type'] = 'success';
                    $_SESSION['message']['text'] = 'Entries deleted successfully.';
                    header('Location: ' . $_SESSION['referrer']);
                    exit;
                case 'invoices':
                    $stmt = HOA\Service::executeStatement('
SELECT
  `parcels`.`id`,
  `parcels`.`data`->>"$.house_number" AS `house_number`,
  `parcels`.`data`->>"$.street" AS `street`,
  `parcels`.`data`->>"$.city" AS `city`,
  `parcels`.`data`->>"$.state" AS `state`,
  `parcels`.`data`->>"$.zip" AS `zip`,
  `parcels`.`data`->>"$.owner" AS `owner`,
  MAX(`receivables`.`date`) AS `date`
FROM
  `' . HOA\Settings::get('table_prefix') . 'parcels` AS `parcels`
  JOIN `' . HOA\Settings::get('table_prefix') . 'receivables` AS `receivables` ON `receivables`.`parcel` = `parcels`.`id`
WHERE
  `parcels`.`data`->>"$.house_number" != ""
  AND `parcels`.`id` IN (' . implode(',', array_fill(0, count($_POST['ids']), '?')) . ')
GROUP BY `parcels`.`id`
HAVING
  SUM(`receivables`.`amount`) < 0
                    ', array_map(function($id) { return ['value' => $id, 'type' => \PDO::PARAM_INT]; }, $_POST['ids']));
                    HOA\PdfUtility::invoices($stmt);
                case 'address-labels':
                    $stmt = HOA\Service::executeStatement('
SELECT
  `data`->>"$.owner" AS `owner`,
  CONCAT_WS(" ", `data`->>"$.house_number", `data`->>"$.street") AS `address`
FROM
  `' . HOA\Settings::get('table_prefix') . 'parcels` AS `parcels`
WHERE
  `data`->>"$.house_number" != ""
  AND `data`->>"$.street" != ""
' . (count($_POST['ids']) ? 'AND `id` IN (' . implode(',', array_fill(0, count($_POST['ids']), '?')) . ')' : '') . '
                ', array_map(function($id) { return ['value' => $id, 'type' => \PDO::PARAM_INT]; }, $_POST['ids'])
                    );
                    HOA\PdfUtility::labels($_POST['label'], $stmt, HOA\Settings::get('labels')['parcels'], $_POST['offset'] ?? 0);
                    case 'return-labels':
                    HOA\PdfUtility::labels($_POST['label'], $_POST['ids'], HOA\Settings::get('labels')['returns'], $_POST['offset'] ?? 0);
                default:
                    throw new Exception('Unrecognized batch command: ' . $_POST['batch']);
            }
        } else if (isset($_POST['edit'])) {
            HOA\Service::executeStatement('UPDATE `parcels` SET `id` = ?, `data` = ? WHERE `id` = ?', [
                ['value' => str_replace('-', '', $_POST['id']), 'type' => \PDO::PARAM_INT],
                ['value' => '{"house_number": "' . $_POST['house_number'] . '", "street": "' . $_POST['street'] . '", "owner": "' . $_POST['owner'] . '"}', 'type' => \PDO::PARAM_STR],
                ['value' => str_replace('-', '', $_POST['edit']), 'type' => \PDO::PARAM_INT]
            ]);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Parcel updated successfully';
        } else {
            HOA\Service::executeStatement('INSERT INTO `' . HOA\Settings::get('table_prefix') . 'parcels` SET `id` = ?, `data` = ?', [
                ['value' => str_replace('-', '', $_POST['id']), 'type' => \PDO::PARAM_INT],
                ['value' => '{"house_number": "' . $_POST['house_number'] . '", "street": "' . $_POST['street'] . '"}', 'type' => \PDO::PARAM_STR]
            ]);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Parcel added successfully';
        }
        header('Location: ?');
        exit;
    } else if (isset($_GET['id']) && isset($_GET['owner'])) {
        if (!$user['admin']) {
            throw new Exception('You are not authorized to update parcels');
        }
        HOA\Service::executeStatement('UPDATE `parcels` SET `data` = JSON_SET(`data`, "$.owner", ?) WHERE `id` = ?', [
            ['value' => $_GET['owner'], 'type' => \PDO::PARAM_STR],
            ['value' => $_GET['id'], 'type' => \PDO::PARAM_INT]
        ]);
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Parcel updated successfully.';
        header('Location: ?');
        exit;
    } else if (isset($_GET['delete'])) {
        if (!$user['admin']) {
            throw new Exception('You are not authorized to delete parcels');
        }
        HOA\Service::executeStatement('DELETE FROM `' . HOA\Settings::get('table_prefix') . 'parcels` WHERE `id` = ?', [
            ['value' => $_GET['delete'], 'type' => \PDO::PARAM_INT]
        ]);
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Parcel deleted successfully.';
        header('Location: ?');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['message']['type'] = 'danger';
    $_SESSION['message']['text'] = $e->getMessage();
}

require_once 'includes/views/start.php';
if ($user['admin']) {
    require_once 'includes/views/parcels.php';
} else {
    echo '<div class="alert alert-danger" role="alert">You are not authorized to view this page.</div>';
}
require_once 'includes/views/end.php';

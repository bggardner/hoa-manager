<main class="container my-3 d-grid gap-3">
<?php
HOA\ViewUtility::displayMessage();
HOA\ViewUtility::addForms([
    HOA\ViewUtility::LEDGER_ENTRY_FORM,
    HOA\ViewUtility::CATEGORY_FORM,
    HOA\ViewUtility::ACCOUNT_FORM
]);
?>
<form>
  <h5>Ledger</h5>
    <div class="input-group">
      <div class="input-group-text"><i class="<?= HOA\ViewUtility::ICONS['new-account'] ?>"></i></div>
      <select name="account" class="form-select">
<?php

if (count(HOA\ViewUtility::get('accounts'))) {
    foreach (HOA\ViewUtility::get('accounts') as $account) {
        echo '
        <option value="' . $account['id'] . '"' . (($_GET['account'] ?? null) == $account['id'] ? ' selected' : '') . '>' . $account['name'] . '</option>';
    }
} else {
    echo '
        <option disabled>Please add an account</option>';
}
$start = $_GET['start'] ?? date('Y-m-d', mktime(0, 0, 0, 1, 1, date('Y')));
$end = $_GET['end'] ?? date('Y-m-d', mktime(0, 0, 0, 12, 31, date('Y')));
?>
      </select>
      <div class="input-group-text">Start:</div>
      <input type="date" name="start" class="form-control" value="<?= $start ?>">
      <div class="input-group-text">End:</div>
      <input type="date" name="end" class="form-control" value="<?= $end ?>">
      <div class="input-group-text" title="Category"><i class="<?= HOA\ViewUtility::ICONS['category'] ?>"></i></div>
      <select name="category" class="form-select">
<?php
foreach (HOA\ViewUtility::get('categories') as $category) {
    echo '
      <option value="' . $category['id'] . '"' . (($_GET['category'] ?? null) == $category['id'] ? ' selected' : '') . '>
        ' . str_repeat('&nbsp;&nbsp;&nbsp;', max(0, $category['level'] - 2)) . ($category['level'] == 1 ? '' : '&#x2514; ') . $category['name'] . '
      </option>';
}
$sort_fields = [
  'date' => ['direction' => 'DESC', 'type' => 'numeric'],
  'budget' => ['direction' => 'DESC', 'type' => 'numeric'],
  'category' => ['direction' => 'ASC', 'type' => 'alpha'],
  'party' => ['direction' => 'ASC', 'type' => 'alpha'],
  'amount' => ['direction' => 'ASC', 'type' => 'numeric'],
  'balance' => ['direction' => 'DESC', 'type' => 'numeric']
];
?>
    </select>
    <button type="submit" class="btn btn-primary"><i class="<?= HOA\ViewUtility::ICONS['filter'] ?>"></i></button>
  </div>
</form>
<form id="ledgerForm" method="post">
<table class="table table-striped table-hover editable-rows">
  <thead>
    <tr>
      <th scope="col">Date<?= HOA\ViewUtility::sortLink('date', $sort_fields) ?></th>
      <th scope="col">Budget<?= HOA\ViewUtility::sortLink('budget', $sort_fields) ?></th>
      <th scope="col">Category<?= HOA\ViewUtility::sortLink('category', $sort_fields) ?></th>
      <th scope="col">Party <?= HOA\ViewUtility::sortLink('party', $sort_fields) ?></th>
      <th scope="col" class="text-end">Amount <?= HOA\ViewUtility::sortLink('amount', $sort_fields) ?></th>
      <th scope="col" class="text-end">Balance<?= HOA\ViewUtility::sortLink('balance', $sort_fields) ?></th>
      <th scope="col" style="width: 0"></th>
    </tr>
  </thead>
  <tbody>
<?
$entries = false;
$stmt = HOA\Service::executeStatement('
SET @balance := COALESCE((SELECT SUM(`amount`) FROM `' . HOA\Settings::get('table_prefix') . 'ledger` WHERE `date` < ?), 0);
SELECT
  `ledger`.`id`,
  `ledger`.`date`,
  `ledger`.`budget`,
  `categories`.`name` AS `category`,
  `ledger`.`party`,
  `ledger`.`amount`,
  `ledger`.`balance`
FROM
(
  SELECT
    `ledger`.*,
    (@balance := @balance + `ledger`.`amount`) AS `balance`
  FROM
    `' . HOA\Settings::get('table_prefix') . 'ledger`
  WHERE
    `ledger`.`account` = ?
    AND `date` BETWEEN ? AND ?
) AS `ledger` /* ledger_v */
LEFT JOIN `' . HOA\Settings::get('table_prefix') . 'accounting_categories` AS `categories` ON `ledger`.`category` = `categories`.`id`
WHERE
 `categories`.`id` IN (
    SELECT `id`
    FROM `' . HOA\Settings::get('table_prefix') . 'accounting_categories`
    WHERE `left` BETWEEN (
      SELECT `left` FROM `' . HOA\Settings::get('table_prefix') . 'accounting_categories` WHERE `id` = ?
    ) AND (
      SELECT `right` FROM `' . HOA\Settings::get('table_prefix') . 'accounting_categories` WHERE `id` = ?
    )
  )
' . HOA\ViewUtility::sortClause($sort_fields) . '
', [
    ['value' => $start, 'type' => \PDO::PARAM_STR],
    ['value' => $_GET['account'] ?? current(HOA\ViewUtility::get('accounts')), 'type' => \PDO::PARAM_INT],
    ['value' => $start, 'type' => \PDO::PARAM_STR],
    ['value' => $end, 'type' => \PDO::PARAM_STR],
    ['value' => $_GET['category'] ?? 1, 'type' => \PDO::PARAM_INT],
    ['value' => $_GET['category'] ?? 1, 'type' => \PDO::PARAM_INT]
]);
$stmt->nextRowset();
while ($row = $stmt->fetch()) {
    $entries = true;
    echo '
    <tr>
      <td>' . $row['date'] . '</td>
      <td>' . $row['budget'] . '</td>
      <td>' . $row['category'] . '</td>
      <td>' . $row['party'] . '</td>
      <td class="amount text-end">
        <input type="hidden" name="id" value="' . $row['id'] . '" disabled>
        <span>$' . number_format($row['amount'], 2) . '</span>
        <div class="input-group d-none">
          <div class="input-group-text px-1"><i class="' . HOA\ViewUtility::ICONS['amount'] . '"></i></div>
          <input type="number" min="-999999.99" max="999999.99" step=".01" class="form-control" name="amount" value="' . $row['amount'] . '" disabled>
        </div>
      </td>
      <td class="text-end">$' . number_format($row['balance'], 2) . '</td>
      <td class="align-middle">
       <button type="button" class="btn btn-sm btn-warning" data-role="edit" title="Edit"><i class="' . HOA\ViewUtility::ICONS['edit'] . '"></i></button>
        <div class="d-none d-flex gap-3 justify-content-end">
          <button type="submit" name="edit" value="1" class="btn btn-sm btn-success" title="Save"><i class="' . HOA\ViewUtility::ICONS['save'] . '"></i></button>
          <button type="submit" name="delete" value="1" class="btn btn-sm btn-danger" title="Delete"><i class="' . HOA\ViewUtility::ICONS['delete'] . '"></i></button>
          <button type="button" class="btn btn-sm btn-secondary" data-role="cancel" title="Cancel"><i class="' . HOA\ViewUtility::ICONS['undo'] . '"></i></button>
        </div>
     </td>
    </tr>';
}
if (!$entries) {
    echo '
    <tr>
      <td colspan="7" class="fst-italic">No entries found</td>
    </tr>';
}
?>
  </tbody>
</table>
</form>
</main>

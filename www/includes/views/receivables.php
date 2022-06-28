<main class="container d-grid gap-3 my-3">
<?php
HOA\ViewUtility::displayMessage();
?>
<form method="post">
  <div class="input-group">
    <div class="input-group-text alert-success"><i class="<?= HOA\ViewUtility::ICONS['new-receivables-entry'] ?>"></i></div>
    <div class="input-group-text" title="Parcel"><i class="<?= HOA\ViewUtility::ICONS['parcels'] ?>"></i></div>
    <select name="parcel" class="form-select" required>
      <option value="*">All</option>
<?php
$parcels_exists = false;
$stmt = HOA\Service::executeStatement('
SELECT
  `id`,
  `data`->>"$.house_number" AS `house_number`,
  `data`->>"$.street" AS `street`
FROM `' . HOA\Settings::get('table_prefix') . 'parcels`
WHERE `data`->>"$.house_number" != ""
ORDER BY `house_number`, `street`
');
while ($row = $stmt->fetch()) {
    $parcels_exist = true;
    echo '
      <option value="' . $row['id'] . '"' . (($_POST['parcel'] ?? null) == $row['id'] ? ' selected' : '' ) . '>
        ' . $row['house_number'] . ' ' . $row['street'] . '
        (' . preg_replace('/(\d{3})(\d{2})(\d{3})/', '\1-\2-\3', $row['id']) . ')
      </option>';
}
if (!$parcels_exist) {
    echo '
      <option disabled>Please add a parcel</option>';
}
$sort_fields = [
  'date' => ['direction' => 'DESC', 'type' => 'numeric'],
  'parcel' => ['direction' => 'ASC', 'type' => 'numeric'],
  'house_number' => ['direction' => 'ASC', 'type' => 'numeric'],
  'street' => ['direction' => 'ASC', 'type' => 'alpha'],
  'description' => ['direction' => 'ASC', 'type' => 'alpha'],
  'amount' => ['direction' => 'ASC', 'type' => 'numeric']
];

?>
    </select>
    <div class="input-group-text" title="Date"><i class="<?= HOA\ViewUtility::ICONS['date'] ?>"></i></div>
    <input name="date" type="date" class="form-control" value="<?= $_POST['date'] ?? date('Y-m-d') ?>" placeholder="Date" required>
    <div class="input-group-text" title="Description"><i class="<?= HOA\ViewUtility::ICONS['text'] ?>"></i></div>
    <input name="description" type="text" class="form-control" value="<?= $_POST['description'] ?? '' ?>" placeholder="Description" required>
    <div class="input-group-text" title="Amount"><i class="<?= HOA\ViewUtility::ICONS['amount'] ?>"></i></div>
    <input name="amount" type="number" min="-999999.99" max="999999.99" step=".01" class="form-control" value="<?= $_POST['amount'] ?? '' ?>" placeholder="Amount" required>
    <button type="submit" class="btn btn-success"><i class="<?= HOA\ViewUtility::ICONS['add'] ?>"></i></button>
  </div>
</form>
<div>
<form>
  <div class="d-flex gap-3">
    <h5 class="col border-2 border-bottom pb-1"><i class="<?= HOA\ViewUtility::ICONS['receivables'] ?> me-2"></i>Receivables</h5>
    <div class="col-auto">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Search" value="<?= $_GET['q'] ?? '' ?>">
        <button type="submit" class="btn btn-primary" title="Search"><i class="<?= HOA\ViewUtility::ICONS['search'] ?>"></i></buttion>
      </div>
    </div>
  </div>
</form>
<form method="post">
<div class="table-responsive">
  <table class="table table-striped table-hover align-middle editable-rows">
    <thead>
      <tr>
        <th scope="col">
          <input type="checkbox" class="form-check-input" data-check="all">
        </th>
        <th scope="col">Date<?= HOA\ViewUtility::sortLink('date', $sort_fields) ?></th>
        <th scope="col">Parcel<?= HOA\ViewUtility::sortLink('parcel', $sort_fields) ?></th>
        <th scope="col">House Number<?= HOA\ViewUtility::sortLink('house_number', $sort_fields) ?></th>
        <th scope="col">Street<?= HOA\ViewUtility::sortLink('street', $sort_fields) ?></th>
        <th scope="col">Description<?= HOA\ViewUtility::sortLink('street', $sort_fields) ?></th>
        <th scope="col">Amount<?= HOA\ViewUtility::sortLink('amount', $sort_fields) ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
<?php
$filter = ['clause' => 'WHERE 1', 'params' => []];
if (isset($_GET['parcel'])) {
  $filter['clause'] = ' AND `parcel` = ?';
  $filter['params'][] = ['value' => $_GET['parcel'], 'type' => \PDO::PARAM_INT];
}
if (isset($_GET['q'])) {
    $filter['clause'] .= ' AND (
  `receivables`.`date` LIKE ?
  OR `parcels`.`id` = ?
  OR `parcels`.`data`->>"$.house_number" = ?
  OR CAST(`parcels`.`data`->>"$.street" AS CHAR) LIKE ?
  OR `description` LIKE ?
  OR `description` LIKE ?
  OR `amount` = ?
)';
    $filter['params'][] = ['value' => $_GET['q'] . '%', 'type' => \PDO::PARAM_STR];
    $filter['params'][] = ['value' => str_replace('-', '', $_GET['q']), 'type' => \PDO::PARAM_INT];
    $filter['params'][] = ['value' => $_GET['q'], 'type' => \PDO::PARAM_INT];
    $filter['params'][] = ['value' => $_GET['q'] . '%', 'type' => \PDO::PARAM_STR];
    $filter['params'][] = ['value' => $_GET['q'] . '%', 'type' => \PDO::PARAM_STR];
    $filter['params'][] = ['value' => '% ' . $_GET['q'] . '%', 'type' => \PDO::PARAM_STR];
    $filter['params'][] = ['value' => $_GET['q'], 'type' => \PDO::PARAM_STR];
}
$stmt = HOA\Service::executeStatement('
SELECT
  `receivables`.`id`,
  `parcels`.`id` AS `parcel`,
  `parcels`.`data`->>"$.house_number" AS `house_number`,
  `parcels`.`data`->>"$.street" AS `street`,
  `receivables`.`date`,
  `receivables`.`description`,
  `receivables`.`amount`
FROM
  `receivables`
  JOIN `parcels` ON `receivables`.`parcel` = `parcels`.`id`
' . $filter['clause'] . '
' . HOA\ViewUtility::sortClause($sort_fields) . '
', $filter['params']);
$entries = false;
while ($row = $stmt->fetch()) {
    $entries = true;
    echo '
      <tr>
        <td>
          <input type="hidden" name="id" value="' . $row['id'] . '" disabled>
          <input type="checkbox" class="form-check-input" name="ids[]" value="' . $row['id'] . '">
        </td>
        <td>' . $row['date'] . '</td>
        <td>' . preg_replace('/(\d{3})(\d{2})(\d{3})/', '\1-\2-\3', $row['parcel']) . '</td>
        <td>' . $row['house_number'] . '</td>
        <td>' . $row['street'] . '</td>
        <td>
          <input type="text" class="form-control d-none" name="description" value="' . $row['description'] . '" placeholder="Description" disabled>
          <span>' . $row['description'] . '</span>
        </td>
        <td>
          <div class="input-group d-none">
            <div class="input-group-text px-1"><i class="' . HOA\ViewUtility::ICONS['amount'] . '"></i></div>
            <input type="number" min="-999999.99" max="999999.99" step="0.01" class="form-control" name="amount" value="' . $row['amount'] . '" placeholder="Amount" disabled>
          </div>
          <span>$' . number_format($row['amount'], 2) . '</span>
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-sm btn-warning" data-role="edit" title="Edit Entry"><i class="' . HOA\ViewUtility::ICONS['edit'] . '"></i></button>
          <div class="d-none d-flex gap-3 justify-content-end">
            <button type="submit" class="btn btn-sm btn-success" name="edit" value="' . $row['id'] . '" data-role="save" title="Save" disabled><i class="' . HOA\ViewUtility::ICONS['save'] . '"></i></button>
            <button type="submit" class="btn btn-sm btn-danger" name="delete" value="' . $row['id'] . '" data-role="delete" title="Delete Entry"><i class="' . HOA\ViewUtility::ICONS['delete'] . '"></i></button>
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
    <tfoot>
      <tr>
        <td colspan="7" class="ps-0">
          <div class="input-group dropup">
            <div class="input-group-text"><i class="<?= HOA\ViewUtility::ICONS['batch'] ?>"></i></div>
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Batch</button>
            <ul class="dropdown-menu">
              <li><button type="submit" name="batch" value="delete" class="btn text-danger" data-role="delete"><i class="<?= HOA\ViewUtility::ICONS['delete'] ?> me-2"></i>Delete</button></li>
            </ul>
          </div>
        </td>
      </tr>
    </tfoot>
  </table>
</div>
</form>
</div>
</main>

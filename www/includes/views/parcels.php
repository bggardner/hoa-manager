<main class="container d-grid gap-3 my-3">
<?php
HOA\ViewUtility::displayMessage();
?>
<form method="post">
  <input type="hidden" name="csrfToken" value="<?= $_SESSION['csrfToken'] ?>">
  <div class="input-group">
    <div class="input-group-text alert-success" title="New Parcel"><i class="<?= HOA\ViewUtility::ICONS['new-parcel'] ?>"></i></div>
    <div class="input-group-text" title="Parcel ID"><i class="<?= HOA\ViewUtility::ICONS['id-number'] ?>"></i></div>
    <input name="id" type="number" min="0" max="99999999" step="1" class="form-control" value="<?= $_POST['id'] ?? '' ?>" placeholder="Parcel ID (XXX-XX-XXX)" required>
    <div class="input-group-text" title="House Number"><i class="<?= HOA\ViewUtility::ICONS['house-number'] ?>"></i></div>
    <input name="house_number" type="text" class="form-control" value="<?= $_POST['house_number'] ?? '' ?>" placeholder="House Number">
    <div class="input-group-text" title="Street"><i class="<?= HOA\ViewUtility::ICONS['street'] ?>"></i></div>
    <input name="street" type="text" class="form-control" value="<?= $_POST['street'] ?? '' ?>" placeholder="Street">
    <button type="submit" class="btn btn-success" title="Add Parcel"><i class="bi-plus-lg"></i></button>
  </div>
</form>
<?
$sort_fields = [
  'balance' => ['direction' => 'ASC', 'type' => 'numeric'],
  'street' => ['direction' => 'ASC', 'type' => 'numeric'],
  'house_number' => ['direction' => 'ASC', 'type' => 'alpha'],
  'id' => ['direction' => 'ASC', 'type' => 'alpha'],
  'owner' => ['direction' => 'ASC', 'type' => 'numeric']
];
?>
<form method="post">
  <input type="hidden" name="csrfToken" value="<?= $_SESSION['csrfToken'] ?>">
  <h5 class="border-2 border-bottom pb-1"><i class="<?= HOA\ViewUtility::ICONS['parcels'] ?> me-2"></i>Parcels</h5>
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle editable-rows" id="parcels">
      <thead>
        <tr>
          <th scope="col">
            <input type="checkbox" class="form-check-input" data-check="all" title="Check All">
          </th>
          <th scope="col">ID<?= HOA\ViewUtility::sortLink('id', $sort_fields) ?></th>
          <th scope="col">House Number<?= HOA\ViewUtility::sortLink('house_number', $sort_fields) ?></th>
          <th scope="col">Street<?= HOA\ViewUtility::sortLink('street', $sort_fields) ?></th>
          <th scope="col" class="text-end">Balance<?= HOA\ViewUtility::sortLink('balance', $sort_fields) ?></th>
          <th scope="col">Owner<?= HOA\ViewUtility::sortLink('owner', $sort_fields) ?></th>
          <th scope="col" class="text-end">
            <button type="button" class="btn btn-sm btn-primary" data-role="county-data"><i class="bi-cloud-arrow-down me-2"></i>County Data</button>
          </th>
        </tr>
      </thead>
      <tbody>
<?php
$stmt = HOA\Service::executeStatement('
SELECT
  `parcels`.`id`,
  `parcels`.`data`->>"$.house_number" AS `house_number`,
  `parcels`.`data`->>"$.street" AS `street`,
  `parcels`.`data`->>"$.owner" AS `owner`,
  SUM(COALESCE(`receivables`.`amount`, 0)) AS `balance`,
  MAX(`receivables`.`date`) AS `date`
FROM
  `' . HOA\Settings::get('table_prefix') . 'parcels` AS `parcels`
  LEFT JOIN `' . HOA\Settings::get('table_prefix') . 'receivables` AS `receivables` ON `receivables`.`parcel` = `parcels`.`id`
GROUP BY
  `parcels`.`id`
' . HOA\ViewUtility::sortClause($sort_fields) . '
');
$today = new DateTime('today');
while ($row = $stmt->fetch()) {
    echo '
        <tr data-id="' . $row['id'] . '">
          <td>
            ' . ($row['house_number'] && $row['street'] ? '<input type="checkbox" class="form-check-input" name="ids[]" value="' . $row['id'] . '">' : '') . '
          </td>
          <td>
            <input type="number" min="0" max="99999999" step="1" class="form-control d-none" name="id" value="' . $row['id'] . '" disabled required>
            <span>' . preg_replace('/(\d{3})(\d{2})(\d{3})/', '\1-\2-\3', $row['id']) . '</span>
          </td>
          <td>
            <input type="text" class="form-control d-none" name="house_number" value="' . $row['house_number'] . '" disabled>
            <span>' . $row['house_number'] . '</span>
          </td>
          <td>
            <input type="text" class="form-control d-none" name="street" value="' . $row['street'] . '" disabled>
            <span>' . $row['street'] . '</span>
          </td>
          <td>
            <div class="d-flex gap-2 justify-content-end">
              <span>$' . number_format($row['balance'], 2) . '</span>';
    if ($row['balance'] < 0) {
        if (DateTime::createFromFormat('!Y-m-d', $row['date']) >= $today) {
            echo '
              <span class="text-warning" title="Outstanding"><i class="bi-hourglass-split"></i></span>';
        } else {
            echo '
              <span class="text-danger" title="Overdue"><i class="bi-alarm-fill"></i></span>';
        }
    } else {
        echo '
              <span class="text-success" title="Paid"><i class="bi-check-circle-fill"></i></span>';
    }
    echo '
          </td>
          <td>
            <input type="text" class="form-control d-none" name="owner" value="' . $row['owner'] . '" disabled>
            <span>' . $row['owner'] . '</span>
          </td>
          <td>
            <div class="d-flex gap-3 justify-content-end">
              <button type="button" class="btn btn-sm btn-warning" data-role="edit" title="Edit Parcel"><i class="bi-pencil"></i></button>
              <button type="submit" class="d-none btn btn-sm btn-success" name="edit" value="' . $row['id'] . '" data-role="save" title="Save" disabled><i class="' . HOA\ViewUtility::ICONS['save'] . '"></i></button>
              <a class="d-none btn btn-sm btn-danger" data-role="delete" title="Delete Parcel" href="?delete=' . $row['id'] . '"><i class="' . HOA\ViewUtility::ICONS['delete']  . '"></i></a>
              <button type="button" class="d-none btn btn-sm btn-secondary float-end" data-role="cancel" title="Cancel"><i class="' . HOA\ViewUtility::ICONS['undo'] . '"></i></button>
              <div class="dropdown">
                <button type="button" class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"></button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="members.php?parcel=' . $row['id'] . '"><i class="' . HOA\ViewUtility::ICONS['new-member'] . ' me-2"></i>Add Member</a></li>
                  <li><a class="dropdown-item" href="members.php?q=' . $row['id'] . '"><i class="' . HOA\ViewUtility::ICONS['members'] . ' me-2"></i>View Members</a></li>
                  <li><a class="dropdown-item" href="receivables.php?parcel=' . $row['id'] . '"><i class="' . HOA\ViewUtility::ICONS['receivables'] . ' me-2"></i>View Receivables</a></li>
                </ul>
              </div>
            </div>
          </td>
        </tr>';
}
?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="6" class="ps-0">
            <div class="dropup">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="<?= HOA\ViewUtility::ICONS['batch'] ?> me-2"></i>Batch</button>
              <ul class="dropdown-menu">
                <li><button type="submit" name="batch" value="delete" class="btn text-danger" data-role="delete"><i class="bi-trash me-2"></i>Delete</button></li>
                <li><button type="submit" name="batch" value="invoices" formtarget="_blank" class="btn"><i class="bi-receipt me-2"></i>Invoices</button></li>
                <li><button type="submit" name="batch" value="statements" formtarget="_blank" class="btn"><i class="bi-layout-text-window me-2"></i>Statements</button></li>
                <li><button type="submit" name="batch" value="parcels" formmethod="get" formaction="<?= HOA\ViewUtility::getMenuItem('Members')['href'] ?>" class="btn"><i class="bi-people-fill me-2"></i>View Members</button></li>
                <li><button type="submit" name="batch" value="address-labels" formtarget="_blank" data-label="8160" class="btn text-nowrap"><i class="bi-envelope-open me-2"></i>Address Labels</button></li>
                <li><button type="submit" name="batch" value="return-labels" formtarget="_blank" data-label="5267" class="btn text-nowrap"><i class="bi-envelope-open-fill me-2"></i>Return Labels</button></li>
              </ul>
            </div>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
  <input type="hidden" name="offset" value="<?= $_POST['offset'] ?? 0 ?>" disabled>
  <input type="hidden" name="label" value="<?= $_POST['label'] ?? 0 ?>" disabled>
</form>
</main>

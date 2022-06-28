<main class="container d-grid gap-3 my-3">
<?php
HOA\ViewUtility::displayMessage();
?>
<form method="post">
  <div class="input-group">
    <div class="input-group-text alert-success" title="New Member"><i class="<?= HOA\ViewUtility::ICONS['new-member'] ?>"></i></div>
    <div class="input-group-text" title="Parcel"><i class="<?= HOA\ViewUtility::ICONS['parcels'] ?>"></i></div>
    <select name="parcel" class="form-select" required>
<?php
$parcels_exists = false;
$stmt = HOA\Service::executeStatement('
SELECT
  `id`,
  `data`->>"$.house_number" AS `house_number`,
  `data`->>"$.street" AS `street`,
  `data`->>"$.owner" AS `owner`
FROM `' . HOA\Settings::get('table_prefix') . 'parcels`
WHERE `data`->>"$.house_number" != ""
ORDER BY `house_number`, `street`
');
while ($row = $stmt->fetch()) {
    $parcels_exist = true;
    echo '
      <option value="' . $row['id'] . '"' . (($_POST['parcel'] ?? ($_GET['parcel'] ?? null)) == $row['id'] ? ' selected' : '' ) . '>
        ' . $row['house_number'] . ' ' . $row['street'] . '
        (' . $row['owner'] . ')
      </option>';
}
if (!$parcels_exist) {
    echo '
      <option disabled>Please add a parcel</option>';
}
$sort_fields = [
  'last_name' => ['direction' => 'ASC', 'type' => 'alpha'],
  'first_name' => ['direction' => 'ASC', 'type' => 'alpha'],
  'parcel' => ['direction' => 'ASC', 'type' => 'numeric'],
  'house_number' => ['direction' => 'ASC', 'type' => 'numeric'],
  'street' => ['direction' => 'ASC', 'type' => 'alpha'],
  'email' => ['direction' => 'ASC', 'type' => 'alpha'],
  'phone' => ['direction' => 'ASC', 'type' => 'numeric']
];

?>
    </select>
    <div class="input-group-text" title="Email"><i class="<?= HOA\ViewUtility::ICONS['email'] ?>"></i></div>
    <input name="email" type="email" class="form-control" value="<?= $_POST['email'] ?? '' ?>" placeholder="Email" required>
    <button type="submit" class="btn btn-success" title="Add Member"><i class="<?= HOA\ViewUtility::ICONS['add'] ?>"></i></button>
  </div>
</form>
<div>
<form>
  <div class="d-flex gap-3">
    <h5 class="col border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['members'] ?> me-2"></i>Members</h5>
    <div class="col-auto">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Search" value="<?= $_GET['q'] ?? '' ?>">
        <button type="submit" class="btn btn-primary" title="Search"><i class="<?= HOA\ViewUtility::ICONS['search'] ?>"></i></buttion>
      </div>
    </div>
  </div>
</form>
<div class="table-responsive">
  <table class="table table-striped table-hover align-middle">
    <thead>
      <tr>
        <th scope="col">Last Name<?= HOA\ViewUtility::sortLink('last_name', $sort_fields) ?></th>
        <th scope="col">First Name<?= HOA\ViewUtility::sortLink('first_name', $sort_fields) ?></th>
        <th scope="col">Parcel<?= HOA\ViewUtility::sortLink('parcel', $sort_fields) ?></th>
        <th scope="col">House Number<?= HOA\ViewUtility::sortLink('house_number', $sort_fields) ?></th>
        <th scope="col">Street<?= HOA\ViewUtility::sortLink('street', $sort_fields) ?></th>
        <th scope="col">Email<?= HOA\ViewUtility::sortLink('email', $sort_fields) ?></th>
        <th scope="col">Phone<?= HOA\ViewUtility::sortLink('phone', $sort_fields) ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
<?php
$filter = ['clause' => 'WHERE 1', 'params' => []];
if (isset($_GET['q'])) {
    $filter['clause'] .= ' AND (
  CAST(`members`.`data`->>"$.last_name" AS CHAR) LIKE ?
  OR CAST(`members`.`data`->>"$.last_name" AS CHAR) LIKE ?
  OR CAST(`members`.`data`->>"$.first_name" AS CHAR) LIKE ?
  OR CAST(`members`.`data`->>"$.first_name" AS CHAR) LIKE ?
  OR `parcels`.`id` = ?
  OR `parcels`.`data`->>"$.house_number" = ?
  OR CAST(`parcels`.`data`->>"$.street" AS CHAR) LIKE ?
)';
    $filter['params'][] = ['value' => $_GET['q'] . '%', 'type' => \PDO::PARAM_STR];
    $filter['params'][] = ['value' => '% ' . $_GET['q'] . '%', 'type' => \PDO::PARAM_STR];
    $filter['params'][] = ['value' => $_GET['q'] . '%', 'type' => \PDO::PARAM_STR];
    $filter['params'][] = ['value' => '% ' . $_GET['q'] . '%', 'type' => \PDO::PARAM_STR];
    $filter['params'][] = ['value' => str_replace('-', '', $_GET['q']), 'type' => \PDO::PARAM_INT];
    $filter['params'][] = ['value' => $_GET['q'], 'type' => \PDO::PARAM_INT];
    $filter['params'][] = ['value' => $_GET['q'] . '%', 'type' => \PDO::PARAM_STR];
}
if (isset($_GET['batch']) && $_GET['batch'] == 'parcels' && isset($_GET['ids']) && is_array($_GET['ids'])) {
    $filter['clause'] .= ' AND `members`.`parcel` IN (' . implode(',', array_fill(0, count($_GET['ids']), '?')) . ')';
    $filter['params'] = array_merge($filter['params'], array_map(function($id) { return ['value' => $id, 'type' => \PDO::PARAM_INT]; }, $_GET['ids']));
}
$stmt = HOA\Service::executeStatement('
SELECT
  `members`.`id`,
  `members`.`data`->>"$.last_name" AS `last_name`,
  `members`.`data`->>"$.first_name" AS `first_name`,
  `members`.`parcel`,
  `parcels`.`data`->>"$.house_number" AS `house_number`,
  `parcels`.`data`->>"$.street" AS `street`,
  `members`.`email`,
  `members`.`data`->>"$.phone" AS `phone`,
  COUNT(`uploads`.`upload`) AS `uploads`
FROM
  `' . HOA\Settings::get('table_prefix') . 'members` AS `members`
  LEFT JOIN `' . HOA\Settings::get('table_prefix') . 'parcels` AS `parcels` ON `members`.`parcel` = `parcels`.`id`
  LEFT JOIN `' . HOA\Settings::get('table_prefix') . 'member_uploads` AS `uploads` ON `members`.`id` = `uploads`.`member`
' . $filter['clause'] . '
GROUP BY `members`.`id`
' . HOA\ViewUtility::sortClause($sort_fields) . '
', $filter['params']);
$entries = false;
while ($row = $stmt->fetch()) {
    $entries = true;
    $phones = json_decode($row['phone']) ?? [];
    $phone = '';
    foreach ($phones as $key => $value) {
        $phone .= '<a href="tel:+1' . $value . '">' . preg_replace('/(\d{3})(\d{3})(\d{4})/', '(\1) \2-\3', $value) . '</a> - <em>' . $key . '</em><br>';
    }
    echo '
      <tr>
        <td>' . $row['last_name'] . '</td>
        <td>' . $row['first_name'] . '</td>
        <td>' . preg_replace('/(\d{3})(\d{2})(\d{3})/', '\1-\2-\3', $row['parcel']) . '</td>
        <td>' . $row['house_number'] . '</td>
        <td>' . $row['street'] . '</td>
        <td>' . ($row['email'] ? '<a href="mailto:' . $row['email'] . '">' . $row['email'] . '</a>' : '') . '</td>
        <td>' . $phone . '</td>
        <td>
          <div class="d-flex gap-3 align-items-center justify-content-end">
            ' . ($row['uploads'] ? '<i class="' . HOA\ViewUtility::ICONS['uploads'] . '" title="Uploads Attached"></i>' : '') . '
            <a href="' . HOA\ViewUtility::getMenuItem('Profile')['href'] . '?id=' . $row['id'] . '" class="btn btn-sm btn-warning" data-role="edit" title="Edit"><i class="' . HOA\ViewUtility::ICONS['edit'] . '"></i></a>
          </div>
        </td>
      </tr>';
}
if (!$entries) {
    echo '
    <tr>
      <td colspan="8" class="fst-italic">No entries found</td>
    </tr>';
}
?>
    </tbody>
  </table>
</div>
</main>

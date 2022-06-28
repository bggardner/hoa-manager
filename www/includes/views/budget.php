<main class="container my-3 d-grid gap-3">
<?php
HOA\ViewUtility::displayMessage();
HOA\ViewUtility::addForms([
    HOA\ViewUtility::BUDGET_ENTRY_FORM,
    HOA\ViewUtility::CATEGORY_FORM
]);
?>
<div>
<form>
  <div class="d-flex justify-content-between gap-3">
    <h5 class="col border-2 border-bottom">Budget</h5>
    <div class="col-auto">
      <div class="input-group">
        <div class="input-group-text"><i class="<?= HOA\ViewUtility::ICONS['year'] ?>"></i></div>
        <select id="year" name="year" class="form-select">
<?php
$stmt = HOA\Service::executeStatement('SELECT DISTINCT `year` FROM `' . HOA\Settings::get('table_prefix') . 'budget` ORDER BY `year` DESC');
$has_years = false;
while ($year = $stmt->fetchColumn()) {
    $has_years = true;
    echo '
          <option' . ((isset($_GET['year']) ? $_GET['year'] : date('Y')) == $year ? ' selected' : '') . '>' . $year . '</option>';
}
if (!$has_years) {
    echo '
          <option disabled>(Add entry above)</option>';
}
?>
        </select>
        <button type="submit" class="btn btn-primary" title="View Budget"><i class="<?= HOA\ViewUtility::ICONS['filter'] ?>"></i></button>
      </div>
    </div>
  </div>
</form>
<form id="budgetForm" method="post">
  <input type="hidden" name="year" value="<?= $_GET['year'] ?? date('Y') ?>">
  <div class="table-responsive">
<table class="table table-striped table-hover editable-rows">
  <thead>
    <tr>
      <th scope="col">Category</th>
      <th scope="col" class="text-end">Amount</th>
      <th scope="col" style="width: 0"></th>
    </tr>
  </thead>
  <tbody>
<?
$category_totals = [];
foreach (HOA\ViewUtility::get('categories') as $category) {
    $category_totals[$category['id']] = array_merge($category, ['total' => 0]);
}
$stmt = HOA\Service::executeStatement('SELECT * FROM `' . HOA\Settings::get('table_prefix') . 'budget` WHERE `year` = ?', [
    ['value' => $_GET['year'] ?? date('Y'), 'type' => \PDO::PARAM_INT]
]);
while ($row = $stmt->fetch()) {
    $category_totals[$row['category']]['amount'] = $row['amount'];
    $this_category = $category_totals[$row['category']];
    foreach (HOA\ViewUtility::get('categories') as $category) {
        if ($this_category['left'] >= $category['left'] && $this_category['right'] <= $category['right']) {
            $category_totals[$category['id']]['total'] += $row['amount'];
        }
    }
}
$previous_category = ['left' => 1, 'right' => 2 * count(HOA\ViewUtility::get('categories'))];
foreach ($category_totals as $category) {
    $is_total = $category['left'] != $category['right'] - 1 && !array_key_exists('amount', $category);
    if (!$is_total && !array_key_exists('amount', $category)) {
        continue;
    }
    echo '
    <tr' . ($is_total ? ' class="fw-bold border-dark"' : '') . '>
      <td scope="row">' . ($category['id'] == '1' ? 'Net' : str_repeat('&nbsp;&nbsp;&nbsp;', max(0, $category['level'] - 1)) . $category['name']) . '</th>
      <td class="amount text-end">
        <span>$' . number_format($category['total'], 2) . '</span>
        <input type="hidden" name="category" value="' . $category['id'] . '" disabled>
        <div class="input-group d-none">
          <div class="input-group-text px-1"><i class="' . HOA\ViewUtility::ICONS['amount'] . '"></i></div>
          <input type="number" min="-999999.99" max="999999.99" step=".01" class="form-control" name="amount" value="' . $category['total'] . '" disabled>
        </div>
      </td>
      <td class="text-end">';
    if (array_key_exists('amount', $category)) {
        echo '
        <button type="button" class="btn btn-sm btn-warning" data-role="edit" title="Edit"><i class="' . HOA\ViewUtility::ICONS['edit'] . '"></i></button>
        <div class="d-none d-flex gap-3 justify-content-end">
          <button type="submit" class="btn btn-sm btn-success" name="edit" value="1" data-role="save" title="Save" disabled><i class="' . HOA\ViewUtility::ICONS['save'] . '"></i></button>
          <button type="submit" name="delete" value="1" class="btn btn-sm btn-danger" title="Delete" data-role="delete"><i class="' . HOA\ViewUtility::ICONS['delete'] . '"></i></button>
          <button type="button" class="btn btn-sm btn-secondary" data-role="cancel" title="Cancel"><i class="' . HOA\ViewUtility::ICONS['undo'] . '"></i></button>
        </div>';
    }
    echo '
     </td>
    </tr>';
}
?>
  </tbody>
</table>
  </div>
</form>
</div>
</main>

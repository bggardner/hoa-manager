<main class="container my-3">
<?php
HOA\ViewUtility::displayMessage();
if ($user['admin'] && isset($_GET['id'])) {
    $member = HOA\Service::executeStatement('SELECT * FROM `' . HOA\Settings::get('table_prefix') . 'members` WHERE `id` = ?', [
        ['value' => $_GET['id'], 'type' => \PDO::PARAM_INT]
    ])->fetch();
    if ($member === false) {
        $_SESSION['message']['type'] = 'warning';
        $_SESSION['message']['text'] = 'Member does not exist!';
        HOA\ViewUtility::displayMessage();
        require_once 'end.php';
        exit;
    }
    $member['data'] = json_decode($member['data']);
} else {
    $member = $user;
}
?>
<h5 class="border-2 border-bottom pb-1"><i class="<?= HOA\ViewUtility::ICONS['profile'] ?> me-2"></i>Profile</h5>
<form method="post" class="d-grid gap-3" id="profile">
  <input type="hidden" name="csrfToken" value="<?= $_SESSION['csrfToken'] ?>">
  <input type="hidden" name="id" value="<?= $member['id'] ?>">
  <div class="form-floating">
<?php
$parcel = HOA\Service::executeStatement('SELECT * FROM `' . HOA\Settings::get('table_prefix') . 'parcels` WHERE `id` = ?', [
    ['value' => $member['parcel'], 'type' => \PDO::PARAM_INT]
])->fetch();
$parcel['data'] = json_decode($parcel['data']);
?>
    <div class="form-control" id="parcel" readonly>
      <?= $parcel['data']->house_number . ' ' . $parcel['data']->street ?>
    </div>
    <label for="parcel" class="form-label">Parcel</label>
  </div>
  <div class="row g-3">
    <div class="col-md">
  <div class="form-floating">
    <input type="email" class="form-control" name="email" id="email" value="<?= $member['email'] ?>" required>
    <label for="email" class="form-label">Email</label>
  </div>
    </div>
    <div class="col-auto" style="min-width: 10rem;">
  <div class="form-floating">
    <div class="form-control" id="last" readonly>
      <?= $member['last'] ?? 'N/A' ?>
    </div>
    <label for="last" class="form-label">Last&nbsp;Login</label>
  </div>
    </div>
  </div>
<?php
foreach (HOA\Settings::get('user_data') as $key => $default) {
    echo '
  <div class="form-floating">';
    if (is_object($default)) {
        echo '
    <div class="form-control bg-transparent h-auto text-center" id="' . $key . '">
      <div class="row mt-1" data-container="pair">';
        if (property_exists($member['data'], $key) && is_object($member['data'])) {
            foreach ($member['data']->$key as $subkey => $value) {
                echo '
        <div class="input-group my-1">
          <input type="text" class="form-control" name="data[' . $key . '][keys][]" value="' . $subkey . '">
          <input type="text" class="form-control" name="data[' . $key . '][values][]" value="' . $value . '">
          <button type="button" class="btn btn-secondary d-none" data-role="undo" data-target=".input-group" title="Undo"><i class="' . HOA\ViewUtility::ICONS['undo'] . '"></i></button>
          <button type="button" class="btn btn-danger" data-role="remove" data-target=".input-group" title="Delete"><i class="' . HOA\ViewUtility::ICONS['delete'] . '"></i></button>
        </div>';
            }
        }
        echo '
        <div class="input-group my-1">
          <input type="text" class="form-control" name="data[' . $key . '][keys][]" value="" placeholder="Label">
          <input type="text" class="form-control" name="data[' . $key . '][values][]" value="" placeholder="Value">
          <button type="button" class="btn btn-danger d-none" data-role="remove" data-target=".input-group" title="Delete"><i class="' . HOA\ViewUtility::ICONS['delete'] . '"></i></button>
          <button type="button" class="btn btn-secondary d-none" data-role="undo" data-target=".input-group" title="Undo"><i class="' . HOA\ViewUtility::ICONS['undo'] . '"></i></button>
          <button type="button" class="btn btn-success" data-role="add" data-target="[data-container]" title="Add"><i class="' . HOA\ViewUtility::ICONS['add'] . '"></i></button>
        </div>
      </div>
    </div>';
    } else {
        echo '
    <input type="text" class="form-control" name="data[' . $key . ']" value="' . (property_exists($member['data'], $key) ? $member['data']->$key : '') . '">';
    }
    echo '
    <label for="' . $key . '">' . str_replace(' ', '&nbsp;', ucwords(str_replace('_', ' ', $key))) . '</label>
  </div>';
}
?>
  <div class="form-floating">
    <div class="form-control bg-transparent h-auto text-center" id="uploadsLabel">
      <div class="mt-1" data-container="file">
<?php
$stmt = HOA\Service::executeStatement('SELECT * FROM `' . HOA\Settings::get('table_prefix') . 'member_uploads` WHERE `member` = ?', [
    ['value' => $member['id'], 'type' => \PDO::PARAM_INT]
]);
while ($row = $stmt->fetch()) {
    echo '
        <div class="input-group my-1">
          <a class="btn btn-primary" href="' . HOA\Settings::get('web_root') . '/upload.php?hash=' . $row['upload'] . '&name=' . $row['name'] . '"><i class="bi-download"></i></a>
          <input type="text" name="upload_names[]" class="form-control" value=" ' . $row['name'] . '" readonly>
          <input type="hidden" name="uploads[]" value="' . $row['upload'] . '">
          <div class="btn btn-secondary d-none" data-role="undo" data-target=".input-group" title="Undo"><i class="' . HOA\ViewUtility::ICONS['undo'] . '"></i></div>
          <div class="btn btn-danger" data-role="remove" data-target=".input-group" title="Delete"><i class="' . HOA\ViewUtility::ICONS['delete'] . '"></i></div>
        </div>';
}
?>
      </div>
      <div class="input-group mt-1">
        <input type="file" class="form-control" data-upload="multiple" data-target="uploads[]" data-name-field="upload_names[]">
        <div class="input-group-text d-none">
          <span class="spinner-border spinner-border-sm"></span>
        </div>
      </div>
    </div>
    <label for="uploadsLabel">Uploads</label>
  </div>
<?php
if ($user['admin']) {
?>
  <div class="form-floating">
    <div class="form-control bg-transparent h-auto pt-2 pb-1">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="admin" id="admin"<?= $member['admin'] ? ' checked' : '' ?>>
        <label class="form-check-label lh-base" for="admin">Administrator
      </div>
    </div>
  </div>
<?php
}
?>
  <div class="d-flex justify-content-between gap-3">
    <div class="d-flex gap-3">
      <button type="submit" class="btn btn-success"><i class="<?= HOA\ViewUtility::ICONS['save'] ?> me-2"></i>Save</button>
      <a class="btn btn-warning" href="?forgot=1"><i class="<?= HOA\ViewUtility::ICONS['password'] ?> me-2"></i>Change Password</a>
    </div>
<?php
if ($user['admin']) {
?>
    <div class="d-flex gap-3">
      <a class="btn btn-primary" title="Message User" href="<?= HOA\ViewUtility::getMenuItem('Messaging')['href'] ?>?members[]=<?= $member['id'] ?>"><i class="<?= HOA\ViewUtility::ICONS['messaging'] ?> me-2"></i>Send Message</a>
      <a class="btn btn-danger" title="Delete Member" data-role="delete" href="<?= HOA\ViewUtility::getMenuItem('Members')['href'] ?>?delete=<?= $member['id'] ?>"><i class="<?= HOA\ViewUtility::ICONS['delete'] ?> me-2"></i>Delete Member</a>
    </div>
<?php
}
?>
  </div>
</form>
</main>

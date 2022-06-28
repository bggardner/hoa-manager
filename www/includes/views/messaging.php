<main class="container my-3">
<?php
HOA\ViewUtility::displayMessage();
$email = '';
if (isset($_POST['email'])) {
    $email = $_POST['email'];
} else if (isset($_GET['members']) && is_array($_GET['members'])) {
    $email = implode(',', HOA\Service::executeStatement(
        'SELECT `email` FROM `' . HOA\Settings::get('table_prefix') . 'members` WHERE `id` IN (' . implode(',', array_fill(0, count($_GET['members']), '?')) . ')',
        array_map(function($id) { return ['value' => $id, 'type' => \PDO::PARAM_INT]; }, $_GET['members'])
    )->fetchAll(\PDO::FETCH_COLUMN, 0));
}
?>
<h5 class="border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['messaging'] ?> me-2"></i>Messaging</h5>
<form method="post">
  <div class="d-grid gap-3">
    <div class="form-floating">
      <input id="email" type="email" name="email" class="form-control" value="<?= $email ?>" placeholder="Email">
      <label for="email">Email</label>
    </div>
    <div class="form-floating">
      <input id="subject" type="text" name="subject" class="form-control" value="<?= $_POST['subject'] ?? '' ?>" placeholder="Subject">
      <label for="subject">Subject</lable>
    </div>
    <div class="form-floating">
      <textarea id="message" name="message" class="form-control" style="height: 6rem;" value="<? $_POST['message'] ?? '' ?>" placeholder="Message"></textarea>
      <label for="message">Message</label>
    </div>
    <div class="d-flex justify-content-between">
      <button type="submit" class="btn btn-primary"><i class="<?= HOA\ViewUtility::ICONS['send'] ?> me-2"></i>Send</button>
      <a class="btn btn-warning" href="?sync=1">Synchronize<i class="<?= HOA\ViewUtility::ICONS['google'] ?> mx-1"></i>Contacts</a>
    </div>
  </div>
</form>
</main>

<div class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <h4 class="modal-title text-center text-secondary mb-3">Reset Password</h4>
<?php
HOA\ViewUtility::displayMessage();
?>
<form method="post">
  <input type="hidden" name="csrfToken" value="<?= $_SESSION['csrfToken']; ?>">
  <div class="input-group mb-3">
    <div class="input-group-text"><i class="bi-lock-fill"></i></div>
    <input type="password" name="password" class="form-control" placeholder="New Password" required>
  </div>
  <div class="input-group mb-3">
    <div class="input-group-text"><i class="bi-lock-fill"></i></div>
    <input type="password" name="confirm" class="form-control" placeholder="Confirm Password" required>
  </div>
  <div class="d-grid gap-3">
    <button type="submit" class="btn btn-primary">Reset</button>
  </div>
</form>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', event => {
  const modal = document.querySelector('.modal');
  modal.addEventListener('shown.bs.modal', event => {
    modal.querySelector('[name="password"]').focus();
  });
  new bootstrap.Modal(modal).show();
});
</script>


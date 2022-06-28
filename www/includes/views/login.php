<div class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <h4 class="modal-title text-center text-secondary mb-3">Member Login</h4>
<?php
HOA\ViewUtility::displayMessage();
?>
<form method="post" action="?login=1">
  <input type="hidden" name="csrfToken" value="<?= $_SESSION['csrfToken']; ?>">
  <div class="input-group mb-3">
    <div class="input-group-text"><i class="bi-person-fill"></i></div>
    <input type="email" name="email" class="form-control" placeholder="Email" required>
  </div>
  <div class="input-group mb-3">
    <div class="input-group-text"><i class="bi-lock-fill"></i></div>
    <input type="password" name="password" class="form-control" placeholder="Password" required>
  </div>
  <div class="d-grid gap-3">
    <button type="submit" class="btn btn-primary">Login</button>
    <a href="?forgot=1" class="text-secondary text-center">Forgot Password?</a>
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
    modal.querySelector('[name="email"]').focus();
  });
  new bootstrap.Modal(modal).show();
});
</script>


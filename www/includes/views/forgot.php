<div class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <h4 class="modal-title text-center text-secondary mb-3">Forgot Password</h4>
<?php
HOA\ViewUtility::displayMessage();
?>
<form method="post">
  <input type="hidden" name="csrfToken" value="<?= $_SESSION['csrfToken']; ?>">
  <div class="input-group mb-3">
    <div class="input-group-text"><i class="bi-person-fill"></i></div>
    <input type="email" name="email" class="form-control" placeholder="Email" required>
  </div>
  <div class="d-grid gap-3">
    <button type="submit" class="btn btn-primary">Send Reset Code</button>
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

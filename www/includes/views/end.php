<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="js/main.js"></script>
<script>
window['web_root'] = '<?= HOA\Settings::get('web_root'); ?>';
</script>
</body>
</html>
<?php
$_SESSION['referrer'] = $_SERVER['REQUEST_URI'] ?? $_SERVER['PHP_SELF'];
?>

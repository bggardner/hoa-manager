<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand d-flex gap-2 align-items-center" href="<?= HOA\Settings::get('home')['url'] ?>">
      <i class="bi-chevron-left"></i>
      <?= HOA\Settings::get('home')['icon'] ? '<img src="' . HOA\Settings::get('home')['icon'] . '" style="max-height: 1.625rem;">' : '' ?>
      <?= HOA\Settings::get('home')['title'] ? '<span>' . HOA\Settings::get('home')['title'] . '</span>' : '' ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
<?php
foreach (HOA\ViewUtility::MENU_ITEMS as $page) {
    if (count($page) == 0) {
        echo '
          <li class="nav-item mx-1"><hr></li>';
        continue;
    }
    if ($page['admin'] && $user['admin'] == 0) {
        continue;
    }
    echo '
        <li class="nav-item">
          <a class="nav-link' . (basename($_SERVER['PHP_SELF']) ==  $page['href'] ? ' active' : '') . '" href="' . $page['href'] . '">
            <i class="' . $page['icon'] . ' me-1"></i>' . ucwords($page['name']) . '
          </a>
        </li>';
}
?>
      </ul>
    </div>
  </div>
</nav>

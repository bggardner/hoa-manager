<?php

if (isset($_GET['logout'])) {
    unset($_SESSION['user']);
    $_SESSION['message']['type'] = 'success';
    $_SESSION['message']['text'] = 'Logged out successfully';
    header('Location: ' . HOA\Settings::get('web_root') . '/');
    exit;
}

if ($_POST) {
    try {
        if (($_POST['csrfToken'] ?? null) != $_SESSION['csrfToken']) {
            throw new Exception('Possible CSRF attack, execution halted.');
        }
        if (isset($_GET['login'])) {
            $stmt = HOA\Service::executeStatement('SELECT `id`, `hash` FROM `' . HOA\Settings::get('table_prefix') . 'members` WHERE `email` = ?', [
                ['value' => $_POST['email'], 'type' => \PDO::PARAM_STR]
            ]);
            $row = $stmt->fetch();

            if ($row === false || !password_verify($_POST['password'], $row['hash'])) {
                throw new Exception('Invalid email/password combination');
            }
            $_SESSION['user'] = $row['id'];
            HOA\Service::executeStatement('UPDATE `' . HOA\Settings::get('table_prefix') . 'members` SET `last` = CURRENT_TIMESTAMP WHERE `email` = ?', [
                ['value' => $_POST['email'], 'type' => \PDO::PARAM_STR]
            ]);
            header('Location: ' . HOA\Settings::get('web_root') . '/');
            exit;
        }
        if (isset($_GET['forgot'])) {
            if (HOA\Service::executeStatement('SELECT `id` FROM `' . HOA\Settings::get('table_prefix') . 'members` WHERE `email` = ?', [
                ['value' => $_POST['email'], 'type' => \PDO::PARAM_STR]
            ])->fetch() === false) {
                throw new Exception('Email is not registered. Please contact an administrator.');
            }
            $code = base64_encode(random_bytes(18));
            HOA\Service::executeStatement('UPDATE `members` SET `code` = ? WHERE `email` = ?', [
                ['value' => $code, 'type' => \PDO::PARAM_STR],
                ['value' => $_POST['email'], 'type' => \PDO::PARAM_STR]
            ]);
            $url = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/profile.php?reset=' . urlencode($code);
            $success = mail(
                $_POST['email'],
                'Password Reset Request',
                '
<html>
<head><title>Password Reset Request</title></head>
<body>
<p>A request was made to reset the password for this email address.  If you did not initiate this request, please disregard.  Otherwise, click the link below to reset your password.</p>
<a href="' . $url . '">' . $url . '</a>
</body>
</html>
                ',
                implode("\r\n", [
                    'MIME-Version: 1.0',
                    'Content-type: text/html; charset=utf-8',
                    'From: ' . HOA\Settings::get('from_email')
                ])
            );
            if (!$success) {
                throw new Exception('There was a problem processing your request.');
            }
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Please check your email for instructions.';
            header('Location: ' . HOA\Settings::get('web_root') . '/');
            exit;
        }
        if (isset($_GET['reset'])) {
            if ($_POST['password'] != $_POST['confirm']) {
                throw new Exception('Passwords do no match');
            }
            HOA\Service::executeStatement('UPDATE `members` SET `hash` = ?, `code` = NULL WHERE `code` = ?', [
                ['value' => password_hash($_POST['password'], PASSWORD_DEFAULT), 'type' => \PDO::PARAM_STR],
                ['value' => $_GET['reset'], 'type' => \PDO::PARAM_STR]
            ]);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Password reset successfull.';
            header('Location: ' . HOA\Settings::get('web_root') . '/');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['message']['type'] = 'danger';
        $_SESSION['message']['text'] = $e->getMessage();
    }
}

if (isset($_GET['forgot'])) {
    require_once 'includes/views/start.php';
    require_once 'includes/views/forgot.php';
    require_once 'includes/views/end.php';
    exit;
} else if (isset($_GET['reset'])) {
    require_once 'includes/views/start.php';
    require_once 'includes/views/reset.php';
    require_once 'includes/views/end.php';
    exit;
} else if (!isset($_SESSION['user'])) {
    require_once 'includes/views/start.php';
    require_once 'includes/views/login.php';
    require_once 'includes/views/end.php';
    exit;
} else {
    $user = HOA\Service::executeStatement('SELECT * FROM `' . HOA\Settings::get('table_prefix') . 'members` WHERE `id` = ?', [
        ['value' => $_SESSION['user'], 'type' => \PDO::PARAM_INT]
    ])->fetch();
    $user['data'] = json_decode($user['data']);
}

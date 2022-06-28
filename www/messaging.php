<?php

require_once 'includes/config.php';

if (isset($_GET['sync'])) {
    try {
        HOA\Google::synchronizeContacts();
    } catch (Exception $e) {
        $_SESSION['message']['type'] = 'danger';
        $_SESSION['message']['text'] = $e->getMessage();
    }
    if (!isset($_SESSION['message'])) {
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Contacts synchroized successfully';
    }
    header('Location: ' . $_SESSION['referrer']);
    exit;
}

if ($_POST) {
    try {
        $success = mail(
            '',
            $_POST['subject'],
            $_POST['message'],
            [
                'From' => HOA\Settings::get('from_email'),
                'Reply-To' => $user['email'],
                'Bcc' => $_POST['email']
            ]
        );
        if (!$success) {
            throw new Exception('There was a problem sending the message');
        }
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Message sent successfully';
        header('Location: ' . $_SESSION['referrer']);
        exit;
    } catch (Exception $e) {
        $_SESSION['message']['type'] = 'danger';
        $_SESSION['message']['text'] = 'There was a problem sending the message';
    }
}

require_once 'includes/views/start.php';
require_once 'includes/views/messaging.php';
require_once 'includes/views/end.php';

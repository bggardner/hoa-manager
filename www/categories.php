<?php

require_once 'includes/config.php';

if ($_POST) {
    try {
        if (!$user['admin']) {
            throw new Exception('You are not authorized to edit categories');
        }
        if (isset($_POST['id'])) {
            if (isset($_POST['delete'])) { // Delete Category
                HOA\Service::deleteTreeNode('accounting_categories', $_POST['id']);
                $_SESSION['message']['type'] = 'success';
                $_SESSION['message']['text'] = 'Category deleted successfully';
            } else { // Edit Category
                HOA\Service::editTreeNode('accounting_categories', $_POST['id'], $_POST['name'], $_POST['parent']);
                $_SESSION['message']['type'] = 'success';
                $_SESSION['message']['text'] = 'Category updated successfully';
            }
        } else { // Add Category
            HOA\Service::addTreeNode('accounting_categories', $_POST['name'], $_POST['parent']);
            $_SESSION['message']['type'] = 'success';
            $_SESSION['message']['text'] = 'Category added successfully';
        }
    } catch (Exception $e) {
        $_SESSION['message']['type'] = 'danger';
        $_SESSION['message']['text'] = $e->getMessage();
    }
}

header('Location: ' . $_SESSION['referrer']);
exit;

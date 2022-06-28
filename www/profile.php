<?php

require_once 'includes/config.php';

if ($_POST) {
    try {
        if ($_POST['id'] != $user['id'] && !$user['admin']) {
            throw new Exception('You are not authorized to edit other profiles.');
        }
        if (!isset($_POST['admin'])) {
            $admin_count = HOA\Service::executeStatement('SELECT COUNT(*) FROM `' . HOA\Settings::get('table_prefix') . 'members` WHERE `admin` = 1 AND `id` != ?', [
                ['value' => $_POST['id'], 'type' => \PDO::PARAM_INT]
            ])->fetchColumn();
            if (!$admin_count) {
                throw new Exception('Cannot remove last administrator');
            }
        }
        $data = (object) array_intersect_key($_POST['data'], HOA\Settings::get('user_data'));
        foreach ($data as $key => $value) {
            if (!$key || !$value) {
                unset($data->$key);
                continue;
            }
            if (is_array($value)) {
                if (
                    array_key_exists('keys', $value)
                    && is_array($value['keys'])
                    && array_key_exists('values', $value)
                    && is_array($value['values'])
                    && count($value['keys']) == count($value['values'])
                ) {
                    $a = [];
                    foreach ($value['keys'] as $i => $subkey) {
                        if (!$subkey || !$value['values'][$i]) {
                            continue;
                        }
                        $a[$subkey] = $value['values'][$i];
                    }
                    if (count($a)) {
                        $data->$key = (object) $a;
                    } else {
                        unset($data->$key);
                    }
                } else if (
                    count(array_filter(array_keys($value), 'is_numeric')) != count($value)
                    || array_keys($value) !== range(0, count($value) - 1)
                ) {
                      $data->$key = (object) $value;
                }
            }
        }
        HOA\Service::executeStatement('UPDATE `members` SET `email` = ?, `admin` = ?, `data` = ? WHERE `id` = ?', [
            ['value' => $_POST['email'], 'type' => \PDO::PARAM_STR],
            ['value' => isset($_POST['admin']) ? 1 : 0, 'type' => \PDO::PARAM_INT],
            ['value' => json_encode($data, JSON_NUMERIC_CHECK), 'type' => \PDO::PARAM_STR],
            ['value' => $_POST['id'], 'type' => \PDO::PARAM_INT]
        ]);
        if ($_POST['id'] == $user['id']) {
            $user['email'] = $_POST['email'];
            $user['data'] = $data;
        }
        $uploads = [];
        foreach ($_POST['uploads'] ?? [] as $key => $hash) {
            $uploads[] = ['hash' => $hash, 'name' => $_POST['upload_names'][$key]];
        }
        HOA\Service::updateUploads('member', $_POST['id'], $uploads);
        $_SESSION['message']['type'] = 'success';
        $_SESSION['message']['text'] = 'Profile saved successfully.';
        header('Location: ' . $_SESSION['referrer']);
        exit;
    } catch (Exception $e) {
        $_SESSION['message']['type'] = 'danger';
        $_SESSION['message']['text'] = $e->getMessage();
    }
}

require_once 'includes/views/start.php';
require_once 'includes/views/profile.php';
require_once 'includes/views/end.php';

<?php

$errors = [
    0 => 'There is no error, the file uploaded with success',
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'No file was uploaded',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk.',
    8 => 'A PHP extension stopped the file upload.',
];

$response = new stdClass();
try {
    require_once 'includes/config.php';
    switch ($_REQUEST['method'] ?? '(none provided)') {
        case 'autocomplete':
            if (!isset($_GET['field']) || !isset($_GET['term'])) {
                throw new Exception('Autocomplete field and term are required');
            }
            $response->term = $_GET['term'];
            $response->results = HOA\ViewUtility::autocomplete($_GET['field'], $_GET['term']);
            break;
        case 'labels':
            $response->results = [];
            foreach (HOA\PdfUtility::LABELS as $id => $label) {
                $response->results[] = (object) ['id' => $id, 'name' => $label['title']];
            }
            break;
        case 'myplace':
            $response->results = [];
            $stmt = HOA\Service::executeStatement('SELECT DISTINCT LEFT(`id`, 5) FROM `' . HOA\Settings::get('table_prefix') . 'parcels`');
            while ($row = $stmt->fetchColumn()) {
                $response->results = array_merge($response->results, json_decode(json_decode(file_get_contents('https://myplace.cuyahogacounty.us/MyPlaceService.svc/ParcelsByParcelID/' . $row))));
            }
            break;
        case 'plaid':
            if (!$user['admin']) {
                throw new Exception('Only administrators may use this function');
            }
            try {
                $plaid = HOA\Settings::get('plaid');
                switch ($_REQUEST['action']) {
                    case 'create_link_token':
                        $response = $plaid->tokens->create('Members', 'en', ['US'], new \TomorrowIdeas\Plaid\Entities\User(1), ['transactions', 'auth']);
                        break;
                    case 'exchange_public_token':
                        $_SESSION['plaid'] = $plaid->items->exchangeToken($_POST['public_token']);
                        break;
                    default:
                        throw new Exception('Unsupported Plaid action: ' . ($_REQUEST['action'] ?? '(none)'));
                }
            } catch (\TomorrowIdeas\Plaid\PlaidException $e) {
                throw new Exception($e->getResponse()->error_message);
            }
            break;
        case 'upload':
            $response->hash = [];
            foreach ($_FILES as $file) {
                if ($file['error'] ?? false) {
                    throw new Exception($errors[$file['error']]);
                }
                $response->hash[] = HOA\Service::addUpload($file);
            }
            break;
        default:
            throw new Exception('Unrecognized method: ' . ($_REQUEST['method'] ?? '(none)'));
    }
} catch (Exception $e) {
    $response->error = $e->getMessage();
}
header('Content-type: text/javascript');
exit(json_encode($response, JSON_NUMERIC_CHECK));


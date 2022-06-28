<?php

namespace HOA;

class ViewUtility
{
    public const ICONS = [
        'account' => 'bi-bank',
        'address-labels' => 'bi-envelope-open',
        'add' => 'bi-plus-lg',
        'amount' => 'bi-currency-dollar',
        'batch' => 'bi-arrow-90deg-up',
        'budget' => 'bi-piggy-bank',
        'category' => 'bi-tags',
        'close' => 'bi-x-lg',
        'date' => 'bi-calendar-event',
        'delete' => 'bi-trash3',
        'edit' => 'bi-pencil',
        'email' => 'bi-at',
        'filter' => 'bi-funnel',
        'google' => 'bi-google',
        'help' => 'bi-life-preserver',
        'house-number' => 'bi-123',
        'id-number' => 'bi-hash',
        'invoices' => 'bi-receipt',
        'ledger' => 'bi-journal',
        'logout' => 'bi-door-open-fill',
        'members' => 'bi-people-fill',
        'messaging' => 'bi-megaphone',
        'new-account' => 'bi-bank',
        'new-budget-entry' => 'bi-journal-plus',
        'new-category' => 'bi-tags',
        'new-ledger-entry' => 'bi-journal-plus',
        'new-member' => 'bi-person-plus-fill',
        'new-parcel' => 'bi-house-door-fill',
        'new-receivables-entry' => 'bi-journal-plus',
        'outstanding' => 'bi-hourglass-split',
        'overdue' => 'bi-alarm-fill',
        'paid' => 'bi-check-circle-fill',
        'parcels' => 'bi-house-door-fill',
        'parent' => 'bi-caret-up-bill',
        'party' => 'bi-person-fill',
        'password' => 'bi-lock-fill',
        'profile' => 'bi-person-circle',
        'receivables' => 'bi-cash-coin',
        'reports' => 'bi-graph-up-arrow',
        'return-labels' => 'bi-envelope-open-fill',
        'save' => 'bi-save',
        'search' => 'bi-search',
        'send' => 'bi-send',
        'sort-alpha-asc' => 'bi-sort-alpha-down',
        'sort-alpha-desc' => 'bi-sort-alpha-down-alt',
        'sort-numeric-asc' => 'bi-sort-numeric-down',
        'sort-numeric-desc' => 'bi-sort-numeric-down-alt',
        'street' => 'bi-signpost',
        'text' => 'bi-type',
        'undo' => 'bi-arrow-counterclockwise',
        'uploads' => 'bi-paperclip',
        'year' => 'bi-calendar'
    ];

    public const ACCOUNT_FORM = ['title' => 'Account', 'icon' => self::ICONS['new-account']];
    public const BUDGET_ENTRY_FORM = ['title' => 'Budget Entry', 'icon' => self::ICONS['new-budget-entry']];
    public const CATEGORY_FORM = ['title' => 'Category', 'icon' => self::ICONS['new-category']];
    public const LEDGER_ENTRY_FORM = ['title' => 'Ledger Entry', 'icon' => self::ICONS['new-ledger-entry']];

    public const MENU_ITEMS = [
        ['name' => 'Reports', 'href' => 'reports.php', 'admin' => false, 'icon' => self::ICONS['reports']],
        ['name' => 'Budget', 'href' => 'budget.php', 'admin' => true, 'icon' => self::ICONS['budget']],
//        ['name' => 'banking', 'admin' => true, 'icon' => 'bank'],
//        ['name' => 'invoices', 'admin' => true, 'icon' => 'receipt'],
//        ['name' => 'receipts', 'admin' => true, 'icon' => 'cash-coin'],
        ['name' => 'Ledger', 'href' => 'ledger.php', 'admin' => true, 'icon' => self::ICONS['ledger']],
        ['name' => 'Messaging', 'href' => 'messaging.php', 'admin' => true, 'icon' => self::ICONS['messaging']],
        ['name' => 'Members', 'href' => 'members.php', 'admin' => true, 'icon' => self::ICONS['members']],
        ['name' => 'Receivables', 'href' => 'receivables.php', 'admin' => true, 'icon' => self::ICONS['receivables']],
        ['name' => 'Parcels', 'href' => 'parcels.php', 'admin' => true, 'icon' => self::ICONS['parcels']],
        ['name' => 'Profile', 'href' => 'profile.php', 'admin' => false, 'icon' => self::ICONS['profile']],
        ['name' => 'Help', 'href' => 'help.php', 'admin' => true, 'icon' => self::ICONS['help']],
        ['name' => 'Logout', 'href' => '?logout=1', 'admin' => false, 'icon' => self::ICONS['logout']]
    ];

    protected static $menu_items;
    protected static $model = [];
    protected static $query_params;

    public static function addAccountForm($opts)
    {
        echo '
<form method="post" action="accounts.php" id="addAccountForm"' . ($opts[0]['title'] == 'Account' ? '' : ' class="d-none"') . '>
  <div class="input-group">';
        static::addFormMenu($opts, static::ACCOUNT_FORM);
        echo '
    <div class="input-group-text" title="Name"><i class="' . static::ICONS['text'] . '"></i></div>
    <input type="text" name="name" class="form-control" placeholder="Name" required>
    <button type="submit" class="btn btn-success" title="Add Account"><i class="' . static::ICONS['add'] . '"></i></button>
  </div>
</form>';
    }

    public static function addBudgetEntryForm($opts)
    {
        echo '
<form method="post" action="budget.php" id="addBudgetEntryForm"' . ($opts[0]['title'] == 'Budget Entry' ? '' : ' class="d-none"') . '>
  <div class="input-group">';
        static::addFormMenu($opts, static::BUDGET_ENTRY_FORM);
        echo '
    <div class="input-group-text" title="Year"><i class="bi-calendar"></i></div>
    <input name="year" class="form-control" type="number" min="1901" max="2155" step="1" value="' . ($_GET['year'] ?? date('Y')) . '" required>
    <div class="input-group-text" title="Category"><i class="' . static::ICONS['category'] . '"></i></div>
    <select name="category" class="form-select" required>';
        if (count(static::get('categories')) > 1) {
            foreach (static::get('categories') as $category) {
                if ($category['id'] == 1) { continue; }
                echo '
      <option value="' . $category['id'] . '">
        ' . str_repeat('&nbsp;&nbsp;&nbsp;', max(0, $category['level'] - 3)) . ($category['level'] == 2 ? '' : '&#x2514; ') . $category['name'] . '
      </option>';
            }
        } else {
            echo '
      <option disabled>Add a category below</option>';
        }
        echo '
    </select>
    <div class="input-group-text" title="Amount"><i class="bi-currency-dollar"></i></div>
    <input name="amount" type="number" min="-999999.99" max="999999.99" step="0.01" class="form-control" value="0.00" required>
    <button type="submit" class="btn btn-success" title="Add Entry"><i class="' . static::ICONS['add'] . '"></i></button>
  </div>
</form>';
    }

    public static function addCategoryForm($opts)
    {
        echo '
<form method="post" action="categories.php" id="addCategoryForm"' . ($opts[0]['title'] == 'Category' ? '' : ' class="d-none"') . '>
  <div class="input-group">';
        static::addFormMenu($opts, static::CATEGORY_FORM);
        echo '
    <div class="input-group-text" title="Parent"><i class="bi-caret-up-fill"></i></div>
    <select name="parent" class="form-select">';
        foreach (static::get('categories') as $category) {
            echo '
      <option value="' . $category['id'] . '" data-left="' . $category['left'] . '" data-right="' . $category['right'] . '" data-name="' . $category['name'] . '">
        ' . str_repeat('&nbsp;&nbsp;&nbsp;', max(0, $category['level'] - 2)) . ($category['level'] == 1 ? '' : '&#x2514; ') . $category['name'] . '
      </option>';
        }
        echo '
    </select>
    <button type="button" class="btn btn-warning d-none" data-role="edit"><i class="' . static::ICONS['edit'] . '"></i></button>
    <div class="input-group-text" title="Name"><i class="' . static::ICONS['text'] . '"></i></div>
    <input type="text" name="name" class="form-control" placeholder="Name" required>
    <button type="submit" class="btn btn-success"><i class="' . static::ICONS['add'] . '"></i></button>
  </div>
</form>
<form method="post" action="categories.php" id="editCategoryForm" class="d-none">
  <input type="hidden" name="id">
  <div class="input-group">
    <div class="input-group-text alert-warning"><i class="' . static::ICONS['category'] . '"></i></div>
    <div class="input-group-text"><i class="bi-caret-up-fill me-2"></i>Parent:</div>
    <select name="parent" class="form-select">';
        foreach (static::get('categories') as $category) {
            echo '
      <option value="' . $category['id'] . '" data-left="' . $category['left'] . '" data-right="' . $category['right'] . '" data-name="' . $category['name'] . '">
        ' . str_repeat('&nbsp;&nbsp;&nbsp;', max(0, $category['level'] - 2)) . ($category['level'] == 1 ? '' : '&#x2514; ') . $category['name'] . '
      </option>';
        }
        echo '
    </select>
    <div class="input-group-text"><i class="' . static::ICONS['text'] . '"></i></div>
    <input type="text" name="name" class="form-control" placeholder="Name">
    <div class="input-group-text"></div>
    <button type="submit" class="btn btn-success" data-role="edit" title="Save"><i class="' . static::ICONS['save'] . '"></i></button>
    <div class="input-group-text"></div>
    <button type="submit" class="btn btn-danger" data-role="delete" title="Delete" name="delete" value="1"><i class="' . static::ICONS['delete'] . '"></i></button>
    <div class="input-group-text"></div>
    <button type="button" class="btn btn-secondary" data-role="cancel" title="Cancel"><i class="' . static::ICONS['close'] . '"></i></button>
  </div>
</form>';
    }

    protected static function addFormMenu($opts, $active)
    {
        if (count($opts) == 1) {
            echo '
    <div class="input-group-text alert-success" title="New ' . $active['title'] . '"><i class="' . $active['icon'] . '"></i></button></div>';
        } else {
            echo '
    <button type="button" class="btn alert-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="New ' . $active['title'] . '"><i class="' . $active['icon'] . ' me-2"></i></button>
    <ul class="add-menu dropdown-menu">';
            foreach ($opts as $opt) {
                echo '
      <li><button type="button" class="dropdown-item" data-target="add' . str_replace(' ', '', $opt['title']) . 'Form"><i class="' . $opt['icon'] . ' me-2"></i>' . $opt['title'] . '</button></li>';
            }
            echo '
    </ul>';
        }
    }

    public static function addForms($forms)
    {
        foreach ($forms as $form) {
            call_user_func('static::add' . str_replace(' ', '', $form['title']) . 'Form', $forms);
        }
    }

    public static function addLedgerEntryForm($opts)
    {
        echo '
<form method="post" action="ledger.php" id="addLedgerEntryForm"' . ($opts[0]['title'] == 'Ledger Entry' ? '' : ' class="d-none"') . '>
  <div class="input-group">';
        static::addFormMenu($opts, static::LEDGER_ENTRY_FORM);
        echo '
    <div class="input-group-text" title="Account"><i class="' . static::ICONS['account'] . '"></i></div>
    <select name="account" class="form-select" required>';
        if (count(static::get('accounts'))) {
            foreach (static::get('accounts') as $row) {
                echo '
      <option value="' . $row['id'] . '"' . (($_GET['account'] ?? null) == $row['id'] ? ' selected' : '') . '>' . $row['name'] . '</option>';
            }
        } else {
            echo '
      <option disabled>Please add an account</option';
        }
        echo '
    </select>
    <div class="input-group-text" title="Date"><i class="bi-calendar-event"></i></div>
    <input type="date" name="date" class="form-control" value="' . date('Y-m-d') . '" required>
    <div class="input-group-text" title="Budget"><i class="' . static::ICONS['budget'] . '"></i></div>
    <select name="budget" class="form-select">
      <option value="">N/A</option>';
        $stmt = Service::executeStatement('SELECT DISTINCT `year` FROM `' . Settings::get('table_prefix') . 'budget` ORDER BY `year` DESC');
        while ($row = $stmt->fetchColumn()) {
            echo '
      <option' . (date('Y') == $row ? ' selected' : '') . '>' . $row . '</option>';
        }
        echo '
    </select>
    <div class="input-group-text" title="Category"><i class="' . static::ICONS['category'] . '"></i></div>
    <select name="category" class="form-select" required>';
        if (count(static::get('categories')) > 1) {
            foreach (static::get('categories') as $category) {
                if ($category['id'] == 1) { continue; }
                echo '
      <option value="' . $category['id'] . '">
        ' . str_repeat('&nbsp;&nbsp;&nbsp;', max(0, $category['level'] - 3)) . ($category['level'] == 2 ? '' : '&#x2514; ') . $category['name'] . '
      </option>';
            }
        } else {
            echo '
      <option disabled>Please add a category</option>';
        }
        echo '
    </select>
    <div class="input-group-text" title="Party"><i class="' . static::ICONS['party'] . '"></i></div>
    <input type="text" name="party" class="form-control" data-autocomplete="party" placeholder="Party" value="' . ($_POST['party'] ?? '') . '">
    <div class="input-group-text"><i class="bi-currency-dollar"></i></div>
    <input type="number" name="amount" class="form-control" min="-999999.99" max="999999.99" step="0.01" placeholder="Amount" value="' . ($_POST['amount'] ?? '') . '" required>
    <button type="submit" class="btn btn-success"><i class="' . static::ICONS['add'] . '"></i></button>
  </div>
</form>';
    }

    public static function autocomplete($field, $term) {
        switch($field) {
            case 'party':
                $query = '
SELECT DISTINCT `party` AS `label` FROM `' . Settings::get('table_prefix') . 'ledger` WHERE `party` LIKE ? OR `party` LIKE ?
                ';
                break;
            default:
                throw new \Exception('Autocomplete not available for ' . $field);
        }
        $params = [];
        for ($i = 0; $i < substr_count($query, '?') / 2; $i++) {
            $params[] = ['value' => $term . '%', 'type' => \PDO::PARAM_STR];
            $params[] = ['value' => '% ' . $term . '%', 'type' => \PDO::PARAM_STR];
        }
        $stmt = Service::executeStatement(
            $query . ' ORDER BY `label` LIMIT ' . Settings::get('autocomplete_limit'),
            $params
        );
        return $stmt->fetchAll();
    }

    public static function buildQuery($merge = [], $diff_keys = [])
    {
        return http_build_query(static::diffAndMergeQuery($merge, $diff_keys));
    }

    protected static function diffAndMergeQuery($merge = [], $diff_keys = [])
    {
        return array_merge(array_diff_key(static::queryParams(), array_fill_keys($diff_keys, null)), $merge);
    }

    public static function displayMessage() {
        if (isset($_SESSION['message'])) {
            echo '
<div class="alert alert-' . $_SESSION['message']['type'] . ' alert-dismissible fade show" role="alert">
  ' . $_SESSION['message']['text'] . '
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
            unset($_SESSION['message']);
        }
    }

    public static function get($model)
    {
        if (is_null(static::$model[$model])) {
            switch ($model) {
                case 'accounts':
                    static::$model[$model] = Service::executeStatement('SELECT * FROM `accounts`')->fetchAll();
                    break;
                case 'categories':
                    static::$model[$model] = Service::executeStatement('
SELECT
  `node`.`id`,
  `node`.`name`,
  COUNT(`parent`.`id`) AS `level`,
  `node`.`left`,
  `node`.`right`
FROM `' . Settings::get('table_prefix') . 'accounting_categories` AS `node`, `accounting_categories` AS `parent`
WHERE `node`.`left` BETWEEN `parent`.`left` AND `parent`.`right`
GROUP BY `node`.`id`
ORDER BY `node`.`left`
                    ')->fetchAll();
                    break;
                default:
                    throw new \Exception('Unknown model `' . $model . '`');
            }
        }
        return static::$model[$model];
    }

    public static function getMenuItem($name)
    {
        if (!isset(static::$menu_items)) {
            static::$menu_items = [];
            foreach (static::MENU_ITEMS as $item) {
                static::$menu_items[$item['name']] = $item;
            }
        }
        return static::$menu_items[$name];
    }

    protected static function parseSort($fields)
    {
        $existing_sorts = explode(',', static::queryParams()['sort'] ?? '');
        $existing_sort = [];
        foreach ($existing_sorts as $sort) {
            @list($field, $direction) = explode(' ', $sort . ' ');
            $existing_sort[$field] = ['direction' => (strtoupper($direction ?? 'ASC') == 'ASC') ? 'ASC' : 'DESC'];
        }
        // Filter non-matching fields
        $existing_sort = array_intersect_key($existing_sort, $fields);
        // Append remaining fields, maintaining existing order
        return array_replace_recursive($existing_sort, $fields, $existing_sort);
    }

    public static function queryParams()
    {
        if (!isset(static::$query_params)) {
            parse_str($_SERVER['QUERY_STRING'], static::$query_params);
        }
        return static::$query_params;
    }

    public static function sortClause($fields)
    {
        $fields = static::parseSort($fields);
        if (count($fields)) {
            $subclauses = [];
            foreach ($fields as $field => $opts) {
                $subclauses[] = '`' . $field . '` ' . $opts['direction'];
            }
            $clause = 'ORDER BY ' . implode(', ', $subclauses);
        } else {
            $clause = '';
        }
        return $clause;
    }

    public static function sortLink($field, $fields)
    {
        $fields = static::parseSort($fields);
        if (count($fields)) {
            $sorts = [];
            $has_priority = array_key_first($fields) == $field;
            foreach ($fields as $name => $opts) {
                if ($field == $name) {
                    $icon = 'sort-' . $opts['type'] . '-';
                    if ($opts['direction'] == 'ASC') {
                        $icon .= 'asc';
                    } else {
                        $icon .= 'desc';
                    }
                    if ($has_priority) {
                        if ($opts['direction'] == 'ASC') {
                            $opts['direction'] = 'DESC';
                        } else {
                            $opts['direction'] = 'ASC';
                        }
                    }
                    array_unshift($sorts, $name . ' ' . $opts['direction']);
                } else {
                    $sorts[] = $name . ' ' . $opts['direction'];
                }
            }
            $url = static::buildQuery(['sort' => implode(',', $sorts)]);
            if ($has_priority) {
                $link_class = 'link-dark';
            } else {
                $link_class = 'link-secondary';
            }
            $link = '<a href="?' . $url . '" class="' . $link_class . ' ms-1"><i class="' . static::ICONS[$icon] . '"></i></a>';
        } else {
            $link = '';
        }
        return $link;
    }
}

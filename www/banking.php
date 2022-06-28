<?php

require_once 'includes/config.php';

if ($user['admin'] && isset($_GET['unlink'])) {
    unset($_SESSION['plaid']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

require_once 'includes/views/start.php';
HOA\ViewUtility::displayMessage();
?>
<main class="container my-3">
<?php

if (isset($_SESSION['plaid'])) {
    try {
        $plaid = HOA\Settings::get('plaid');
        $response = $plaid->transactions->list($_SESSION['plaid']->access_token, new DateTime('30 days ago'), new DateTime('now'));
        echo '
<h3>Accounts</h3>
<table class="table table-striped table-hover">
  <thead>
    <th>Name</th>
    <th class="text-end">Balance</th>
  </thead>
  <tbody>';
        $fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        $accounts = [];
        foreach ($response->accounts as $account) {
            $accounts[$account->account_id] = $account->name;
            echo '
    <tr>
      <td>' . $account->name . '</td>
      <td class="text-end">' . $fmt->formatCurrency($account->balances->current, $account->balances->iso_currency_code) . '</td>
    </tr>';
        }
        echo '
  </tbody>
</table>
<h3>Transactions</h3>
<table class="table table-striped table-hover">
  <thead>
    <th>Account</th>
    <th>Date</th>
    <th>Party</th>
    <th class="text-end">Amount</th>
  </thead>
  <tbody>';
        foreach ($response->transactions as $transaction) {
            echo '
    <tr>
      <td>' . $accounts[$transaction->account_id] . '</td>
      <td>' . $transaction->date . '</td>
      <td>' . $transaction->name . '</td>
      <td class="text-end">' . $fmt->formatCurrency($transaction->amount, $transaction->iso_currency_code) . '</td>
    </tr>';
        }
        echo '
  </tbody>
</table>';
    } catch (\TomorrowIdeas\Plaid\PlaidRequestException $e) {
        $_SESSION['message']['type'] = 'danger';
        $_SESSION['message']['text'] = $e->getResponse()->error_message;
        HOA\ViewUtility::displayMessage();
        echo '
<a href="?unlink=1" class="btn btn-warning"><i class="bi-unlock me-2"></i>Unlink</a>';
    }
} else {
?>
<button id="link-button" class="btn btn-primary">Link Account</button>
<script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', async event => {
  const plaid = Plaid.create({
    // Create a new link_token to initialize Link
    token: (await HOA.fetch('method=plaid&action=create_link_token')).link_token,
//    receivedRedirectUri: window.location.href,
    onLoad: function() {
      // Optional, called when Link loads
    },
    onSuccess: function(public_token, metadata) {
      // Send the public_token to your app server.
      // The metadata object contains info about the institution the
      // user selected and the account ID or IDs, if the
      // Account Select view is enabled.
      const data = new FormData();
      data.append('public_token', public_token);
      data.append('csrfToken', '<?= $_SESSION['csrfToken'] ?>');
      HOA.fetch('method=plaid&action=exchange_public_token', {
        method: 'POST',
        body: data
      }).then(data => {
        location.reload();
      });
    },
    onExit: function(err, metadata) {
      // The user exited the Link flow.
      if (err != null) {
        // The user encountered a Plaid API error prior to exiting.
      }
      // metadata contains information about the institution
      // that the user selected and the most recent API request IDs.
      // Storing this information can be helpful for support.
    },
    onEvent: function(eventName, metadata) {
      // Optionally capture Link flow events, streamed through
      // this callback as your users connect an Item to Plaid.
      // For example:
      // eventName = "TRANSITION_VIEW"
      // metadata  = {
      //   link_session_id: "123-abc",
      //   mfa_type:        "questions",
      //   timestamp:       "2017-09-14T14:42:19.350Z",
      //   view_name:       "MFA",
      // }
    }
  });
  document.querySelector('#link-button').addEventListener('click', event => {
    plaid.open();
  });
});
</script>
<?php
}
?>
</main>
<?php
require_once 'includes/views/end.php';
?>


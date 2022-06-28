<main class="container my-3">
<h5 class="border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['help'] ?> me-2"></i>Help - Overview</h5>
<p class="lh-lg">
Many pages have data displayed in tables.
Above the table, there may be a form to add a new entry to the table.
The icon on the left of the form <span class="btn btn-sm alert-success"><i class="<?= HOA\ViewUtility::ICONS['new-ledger-entry'] ?>"></i></span> identifies the type of entry that will be added, and may display a menu when clicked for adding different types of entries.
Clicking the add button <span class="btn btn-sm btn-success"><i class="<?= HOA\ViewUtility::ICONS['add'] ?>"></i></span> will attempt to add the entry to the table and reload the page.
A success or error message will be displayed at the top of the page.
</p>
<p class="lh-lg">
Also above some tables (but below the "add" form) will be a form to filter the data.
This may include drop-downs or text fields, and clicking the search button <span class="btn btn-sm btn-primary"><i class="<?= HOA\ViewUtility::ICONS['search'] ?>"></i></span> or filter button <span class="btn btn-sm btn-primary"><i class="<?= HOA\ViewUtility::ICONS['filter'] ?>"></i></span> will apply the filter.
Searches (as opposed to filters) usually return matches where the query term matches the beginning of a word, and has to match the term exactly (including spaces, but case insensitive).
<p class="lh-lg">
Some table column headers may have sort icons <i class="<?= HOA\ViewUtility::ICONS['sort-alpha-asc'] ?>"></i>.
Clicking on these icons will sort the data by that column.
Clicking on the same icon again will reverse the sort order.
The column with the highest priority (most recent) sort will have a slightly darker icon.
Some table rows may have an edit button <span class="btn btn-sm btn-warning"><i class="<?= HOA\ViewUtility::ICONS['edit'] ?>"></i></span>.
Clicking on this button will make some items in the row editable and display additional action buttons.
Clicking the save button <span class="btn btn-sm btn-success"><i class="<?= HOA\ViewUtility::ICONS['save'] ?>"></i></span> will save the changes and reload the page.
Clicking the delete button <span class="btn btn-sm btn-danger"><i class="<?= HOA\ViewUtility::ICONS['delete'] ?>"></i></span> will delete the row (after confirmation) and reload the page.
Clicking the cancel button <span class="btn btn-sm btn-secondary"><i class="<?= HOA\ViewUtility::ICONS['undo'] ?>"></i></span> will return the items to their original values and remove the action buttons.
</p>
<p class="lh-lg">
Some tables will include a checkbox ( <input type="checkbox" class="form-check-input align-middle mb-2"> ) at the beginning of each row.
Clicking the checkbox in the table header will set the state of the checkbox in every row.
The table footer contains a <span class="dropup"><span class="btn btn-sm btn-outline-secondary dropdown-toggle"><i class="<?= HOA\ViewUtility::ICONS['batch'] ?> me-2"></i>Batch</span></span> menu that will perform the selected operation on the checked rows.
</p>
<h5 class="border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['parcels'] ?> me-2"></i>Parcels</h5>
<p class="lh-lg">
Parcels are the individual property lots within the homeowners association.
They are identified by the Cuyahoga County parcel ID (###-##-##) and may include a house number, street, and owner information.
The owner information can be retrieved from Cuyahoga County's <em>MyPlace</em> service by clicking on <span class="btn btn-sm btn-primary"><i class="bi-cloud-arrow-down me-2"></i>County Data</span> at the top of the table.
If the current owner information differs from the county's, a <span class="btn btn-sm btn-primary"><i class="bi-arrow-left"></i></span> will display.
Clicking on this button will update the parcel with the owner information from the county.
<em>NOTE: Before updating the parcel, delete previous members associated with that parcel, if necessary.</em>
</p>
The <em>Balance</em> column shows the current balance based on receivables entries, along with a status icon:
<ul>
  <li><i class="text-success <?= HOA\ViewUtility::ICONS['paid'] ?>"></i> - Paid (zero balance)</li>
  <li><i class="text-warning <?= HOA\ViewUtility::ICONS['outstanding'] ?>"></i> - Outstanding (negative balance, last entry date in the future)</li>
  <li><i class="text-danger <?= HOA\ViewUtility::ICONS['overdue'] ?>"></i> - Overdue (negative balance, last entry date in the past)</li>
</ul>
Clicking the menu button <span class="btn btn-sm dropdown-toggle"></span> on each row will bring up a menu:
<ul>
  <li><i class="<?= HOA\ViewUtility::ICONS['new-member'] ?> me-2"></i>Add Member - Takes you to the <i class="<?= HOA\ViewUtility::getMenuItem('Members')['icon'] ?> me-1"></i>Members page and selects the parcel in the add member form at the top of the page.</li>
  <li><i class="<?= HOA\ViewUtility::ICONS['members'] ?> me-2"></i>View Members - Takes you to the <i class="<?= HOA\ViewUtility::getMenuItem('Members')['icon'] ?> me-1"></i>Members page and lists the membersassociated with that parcel.</li>
  <li><i class="<?= HOA\ViewUtility::ICONS['receivables'] ?> me-2"></i>View Receivables - Takes you to the <i class="<?= HOA\ViewUtility::getMenuItem('Receivables')['icon'] ?> me-1"></i>Receivables page and lists the entries associated with that parcel.</li>
</ul>
<strong>Batch Operations</strong>
<ul>
  <li><span class="text-danger"><i class="<?= HOA\ViewUtility::ICONS['delete'] ?> me-2"></i>Delete</span> - Deletes all selected rows (after confirmation) and reloads the page.</li>
  <li><i class="<?= HOA\ViewUtility::ICONS['invoices'] ?> me-2"></i>Invoices</span> - Generates a PDF of invoices for selected parcels with non-zero balances in a new browser window.</li>
  <li><i class="<?= HOA\ViewUtility::ICONS['address-labels'] ?> me-2"></i>Address Labels</span> - Opens an label options dialog, then generates a PDF of address labels for the selected parcels in a new browser window.</li>
  <li><i class="<?= HOA\ViewUtility::ICONS['return-labels'] ?> me-2"></i>Return Labels</span> - Opens an label options dialog, then generates a PDF of return labels for the number selected parcels in a new browser window.</li>
</ul>
<h5 class="border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['members'] ?> me-2"></i>Members</h5>
<p class="lh-lg">
Members are login accounts identified by an email address and associated with a parcel.
No two members may have the same email address, but multiple members can be associated with the same parcel.
New members are required to request a password reset via the "Forgot Password?" link at the login screen.
</p>
<p class="lh-lg">
Clicking the edit button <span class="btn btn-sm btn-warning"><i class="<?= HOA\ViewUtility::ICONS['edit'] ?>"></i></span> will take you to the member's profile page.
</p>
<h5 class="border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['profile'] ?> me-2"></i>Profile</h5>
<p class="lh-lg">
The profile page displays member information and defaults to the currently logged-in member.
Phone numbers require a label, and those that do not will be deleted.
To delete phone numbers or attachments, click on the <span class="btn btn-sm btn-danger"><i class="<?= HOA\ViewUtility::ICONS['delete'] ?>"></i></span>, which will strikethrough the item.
To undo the delete, click the <span class="btn btn-sm btn-secondary"><i class="<?= HOA\ViewUtility::ICONS['undo'] ?>"></i></span>.
Any changes, including uploaded files, will not be saved until <span class="btn btn-sm btn-success"><i class="<?= HOA\ViewUtility::ICONS['save'] ?> me-2"></i>Save</span> is clicked.
Clicking <span class="btn btn-sm btn-danger"><i class="<?= HOA\ViewUtility::ICONS['delete'] ?> me-2"></i>Delete Member</span> will delete the member and reload the page.
This will also delete any uploads associated with the member.
</p>
<h5 class="border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['receivables'] ?> me-2"></i>Receivables</h5>
<p class="lh-lg">
Receivables track <em>parcel</em> balances, not <em>member</em> balances, therefore each receivables entry must be associated with a parcel.
The entry date should be the due date or date received.
The <em>Description</em> field can be used to describe the what, who, or how of the receivable (<em>Mr. &amp; Mrs. Smith via Venmo</em> or <em><?= date('Y') ?> Regular Assessment</em>, e.g.).
Entries with negative amounts are monies that are owed to the homeowners association (assessments, fees, interest, etc.), and positive amounts are payments by the homeowner.
</p>
<h5 class="border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['category'] ?> me-2"></i>Categories</h5>
<p class="lh-lg">
Categories do not have their own page, but are used for accounting in the budget and ledger.
They are hierarchical, and must be assigned to a parent category, with the root category being <em>Categories</em>.
It is suggested the the highest level categories (under the root category) be <em>Income</em>, <em>Expenses</em>, and <em>Transfers</em>.
</p>
<h5 class="border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['budget'] ?> me-2"></i>Budget</h5>
<p class="lh-lg">
The budget is used to track estimated amounts per category in a specific year.
Income is normally assigned a positive amount, while expenses are normally assigned negative amounts.
</p>
<p class="lh-lg">
Amounts can be assigned at any level in the category hierarchy, but only once along a "branch".
That is, if a child category has been assign an amount, a parent category cannot be assigned an amount as well.
Likewise, if a parent category has been assigned an amount, a child category cannot be assigned an amount.
</p>
<h5 class="border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['account'] ?> me-2"></i>Accounts</h5>
<p class="lh-lg">
Like categories, accounts do not have their own page, and are only used in the ledger.
They usually represent bank accounts, but can also represent cash-on-hand or earmarked funds.
</p>
<h5 class="border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['ledger'] ?> me-2"></i>Ledger</h5>
<p class="lh-lg">
The ledger is used to track balances of accounts belonging to the homeowners association and is completely separate from receivables.
Therefore, payments from homeowners should be logged in both receivables and the ledger.
</p>
<p class="lh-lg">
Each ledger entry is associated with an account, date, and category, and usually a budget year and party.
The date should be the date was posted to the account, or an estimated posting date to be reconciled later.
If assigned a budget year, it will be included in a budget vs. actuals report.
The party is the payee or payor, and will normally be a vendor in the case of an expense, and a homeowner in the case of income.
Transfers and starting balances do not need a party assigned.
Like budget entries, income is normally assigned a positive amount, while expenses are normally assigned negative amounts.
</p>
<p class="lh-lg">
The ledger entries do not have to match those in a bank statement exactly, although periodic balances should.
The key difference is assigning entries to a budget an category, which is not typically part of a bank statement.
Other differences may include how transfers are logged, as transfers between tracked accounts require two ledger entries: one with a negative amount in the outgoing account and one with a positive amount in the incoming account.
For example, instead of logging a ledger entry for deposits into one account, then another two entries for transferring to another tracked account (as would appear in a bank statement), a single ledger entry could be entered that deposits the funds into the second account directly.
While this may cause a time-shift in the daily balance, it simplifies report generation.
</p>
<h5 class="border-2 border-bottom"><i class="<?= HOA\ViewUtility::ICONS['reports'] ?> me-2"></i>Reports</h5>
<p class="lh-lg">
TODO
</p>
</main>

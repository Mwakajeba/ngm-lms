<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpEmailController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AccountClassGroupController;
use App\Http\Controllers\Accounting\BudgetController;
use App\Http\Controllers\ChartAccountController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CashCollateralTypeController;
use App\Http\Controllers\CashCollateralController;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/verify-sms', [AuthController::class, 'showVerificationForm'])->name('verify-sms');
Route::post('/verify-sms', [AuthController::class, 'verifySmsCode']);

Route::get('/forgotPassword', [AuthController::class, 'showForgotPasswordForm'])->name('forgotPassword');
Route::post('/forgotPassword', [AuthController::class, 'forgotPassword']);

Route::get('/verify-otp-password', [AuthController::class, 'showVerificationForm'])->name('verify-otp-password');
Route::post('/verify-otp-password', [AuthController::class, 'verifyPasswordCode']);

Route::get('/reset-password', [AuthController::class, 'showNewPasswordForm'])->name('new-password-form');
Route::post('/reset-password', [AuthController::class, 'storeNewPassword']);

Route::get('/resend-otp/{phone}', [AuthController::class, 'resendOtp'])->name('resend.otp');

// Language switching
Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');

// Test language route
Route::get('/test-language', function () {
    return view('test-language');
})->name('test.language');

Route::get('/request-email-otp', [OtpEmailController::class, 'showEmailForm'])->name('email-otp-form');
Route::post('/send-email-otp', [OtpEmailController::class, 'sendOtpEmail'])->name('email-otp-send');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

////////////////////////////////////////////// ROLES & PERMISSIONS MANAGEMENT /////////////////////////////////////////////
Route::middleware(['auth'])->group(function () {
    // Roles management
    Route::get('roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::get('roles/create', [RolePermissionController::class, 'create'])->name('roles.create');
    Route::post('roles', [RolePermissionController::class, 'store'])->name('roles.store');
    Route::get('roles/{role}', [RolePermissionController::class, 'show'])->name('roles.show');
    Route::get('roles/{role}/edit', [RolePermissionController::class, 'edit'])->name('roles.edit');
    Route::post('roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [RolePermissionController::class, 'destroy'])->name('roles.destroy');

    // Permissions management
    Route::get('permissions', [RolePermissionController::class, 'permissions'])->name('permissions.index');
    Route::post('permissions', [RolePermissionController::class, 'createPermission'])->name('permissions.store');
    Route::delete('permissions/{permission}', [RolePermissionController::class, 'deletePermission'])->name('permissions.destroy');

    // User role assignment
    Route::post('users/{user}/assign-roles', [RolePermissionController::class, 'assignToUser'])->name('users.assign-roles');
    Route::delete('users/{user}/remove-role', [RolePermissionController::class, 'removeFromUser'])->name('users.remove-role');

    // Role statistics
    Route::get('roles-stats', [RolePermissionController::class, 'getStats'])->name('roles.stats');
});
////////////////////////////////////////////// END ROLES & PERMISSIONS MANAGEMENT //////////////////////////////////////////

////////////////////////////////////////////// USER MANAGEMENT /////////////////////////////////////////////////////

// Additional user routes (must come BEFORE resource route)
Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile')->middleware('auth');
Route::put('/users/profile', [UserController::class, 'updateProfile'])->name('users.profile.update')->middleware('auth');

Route::resource('users', UserController::class)->middleware(['auth', 'company.scope']);

// Additional user routes that require user parameter
Route::patch('/users/{user}/status', [UserController::class, 'changeStatus'])->name('users.status')->middleware(['auth', 'company.scope']);
Route::post('/users/{user}/roles', [UserController::class, 'assignRoles'])->name('users.roles')->middleware(['auth', 'company.scope']);

////////////////////////////////////////////// END /////////////////////////////////////////////////////////////////

////////////////////////////////////////////// SETTINGS ROUTES ////////////////////////////////////////////////

Route::prefix('settings')->name('settings.')->middleware(['auth', 'company.scope'])->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');

    // Company Settings
    Route::get('/company', [SettingsController::class, 'companySettings'])->name('company');
    Route::put('/company', [SettingsController::class, 'updateCompanySettings'])->name('company.update');

    // Branch Settings
    Route::get('/branches', [SettingsController::class, 'branchSettings'])->name('branches');
    Route::get('/branches/create', [SettingsController::class, 'createBranch'])->name('branches.create');
    Route::post('/branches', [SettingsController::class, 'storeBranch'])->name('branches.store');
    Route::get('/branches/{branch}/edit', [SettingsController::class, 'editBranch'])->name('branches.edit');
    Route::put('/branches/{branch}', [SettingsController::class, 'updateBranch'])->name('branches.update');
    Route::delete('/branches/{branch}', [SettingsController::class, 'destroyBranch'])->name('branches.destroy');

    // User Settings
    Route::get('/user', [SettingsController::class, 'userSettings'])->name('user');
    Route::put('/user', [SettingsController::class, 'updateUserSettings'])->name('user.update');

    // System Settings
    Route::get('/system', [SettingsController::class, 'systemSettings'])->name('system');
    Route::put('/system', [SettingsController::class, 'updateSystemSettings'])->name('system.update');
    Route::post('/system/reset', [SettingsController::class, 'resetSystemSettings'])->name('system.reset');
    Route::post('/system/test-email', [SettingsController::class, 'testEmailConfig'])->name('system.test-email');

    // Backup Settings
    Route::get('/backup', [SettingsController::class, 'backupSettings'])->name('backup');
    Route::post('/backup/create', [SettingsController::class, 'createBackup'])->name('backup.create');
    Route::post('/backup/restore', [SettingsController::class, 'restoreBackup'])->name('backup.restore');
    Route::get('/backup/{hash_id}/download', [SettingsController::class, 'downloadBackup'])->name('backup.download');
    Route::delete('/backup/{hash_id}', [SettingsController::class, 'deleteBackup'])->name('backup.delete');
    Route::post('/backup/clean', [SettingsController::class, 'cleanOldBackups'])->name('backup.clean');

    // AI Assistant Settings
    Route::get('/ai', [SettingsController::class, 'aiAssistantSettings'])->name('ai');
    Route::post('/ai/chat', [SettingsController::class, 'aiChat'])->name('ai.chat')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    Route::get('/ai/test', function () {
        return response()->json([
            'csrf_token' => csrf_token(),
            'status' => 'success',
            'message' => 'AI Assistant connection test successful'
        ]);
    })->name('ai.test');

    // Penalty Settings
    Route::get('/penalty', [SettingsController::class, 'penaltySettings'])->name('penalty');
    Route::put('/penalty', [SettingsController::class, 'updatePenaltySettings'])->name('penalty.update');

    // Fees Settings
    Route::get('/fees', [SettingsController::class, 'feesSettings'])->name('fees');
    Route::put('/fees', [SettingsController::class, 'updateFeesSettings'])->name('fees.update');
});

////////////////////////////////////////////// END SETTINGS ROUTES /////////////////////////////////////////////

////////////////////////////////////////////// BRANCH MANAGEMENT ///////////////////////////////////////////////////

//Route::resource('branches', BranchController::class)->middleware('auth');

//Route::resource('companies', CompanyController::class)->middleware('auth');

Route::resource('cash_collateral_types', CashCollateralTypeController::class)->middleware('auth');

////////////////////////////////////////////// END /////////////////////////////////////////////////////////////////

////////////////////////////////////////////// SUPER ADMIN ROUTES ////////////////////////////////////////////////

Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

    // Companies
    Route::get('/companies', [SuperAdminController::class, 'companies'])->name('companies');
    Route::get('/companies/create', [SuperAdminController::class, 'createCompany'])->name('companies.create');
    Route::post('/companies', [SuperAdminController::class, 'storeCompany'])->name('companies.store');
    Route::get('/companies/{company}', [SuperAdminController::class, 'showCompany'])->name('companies.show');
    Route::get('/companies/{company}/edit', [SuperAdminController::class, 'editCompany'])->name('companies.edit');
    Route::put('/companies/{company}', [SuperAdminController::class, 'updateCompany'])->name('companies.update');
    Route::delete('/companies/{company}', [SuperAdminController::class, 'destroyCompany'])->name('companies.destroy');

    // Branches
    Route::get('/branches', [SuperAdminController::class, 'branches'])->name('branches');

    // Users
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
});

////////////////////////////////////////////// END SUPER ADMIN ROUTES /////////////////////////////////////////////

////////////////////////////////////////////// ACCOUNTING MANAGEMENT ///////////////////////////////////////////////

Route::prefix('accounting')->name('accounting.')->middleware('auth')->group(function () {
    // Chart of Accounts - FSLI
    Route::get('/fsli-accounts', [AccountClassGroupController::class, 'index'])->name('fsli-accounts');
    Route::get('/fsli-accounts/create', [AccountClassGroupController::class, 'create'])->name('fsli-accounts.create');
    Route::post('/fsli-accounts', [AccountClassGroupController::class, 'store'])->name('fsli-accounts.store');
    Route::get('/fsli-accounts/{accountClassGroup}', [AccountClassGroupController::class, 'show'])->name('fsli-accounts.show');
    Route::get('/fsli-accounts/{accountClassGroup}/edit', [AccountClassGroupController::class, 'edit'])->name('fsli-accounts.edit');
    Route::put('/fsli-accounts/{accountClassGroup}', [AccountClassGroupController::class, 'update'])->name('fsli-accounts.update');
    Route::delete('/fsli-accounts/{accountClassGroup}', [AccountClassGroupController::class, 'destroy'])->name('fsli-accounts.destroy');

    // Chart of Accounts
    Route::get('/accounts', [ChartAccountController::class, 'index'])->name('accounts');
    Route::get('/accounts/create', [ChartAccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [ChartAccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{chartAccount}', [ChartAccountController::class, 'show'])->name('accounts.show');
    Route::get('/accounts/{chartAccount}/edit', [ChartAccountController::class, 'edit'])->name('accounts.edit');
    Route::put('/accounts/{chartAccount}', [ChartAccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{chartAccount}', [ChartAccountController::class, 'destroy'])->name('accounts.destroy');

    // Suppliers
    Route::get('/suppliers', [App\Http\Controllers\Accounting\SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [App\Http\Controllers\Accounting\SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [App\Http\Controllers\Accounting\SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [App\Http\Controllers\Accounting\SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{supplier}/edit', [App\Http\Controllers\Accounting\SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [App\Http\Controllers\Accounting\SupplierController::class, 'update'])->name('suppliers.update');
    Route::patch('/suppliers/{supplier}/status', [App\Http\Controllers\Accounting\SupplierController::class, 'changeStatus'])->name('suppliers.changeStatus');
    Route::delete('/suppliers/{supplier}', [App\Http\Controllers\Accounting\SupplierController::class, 'destroy'])->name('suppliers.destroy');

    // Manual Journal Entries
    Route::get('/journal-entries', [App\Http\Controllers\Accounting\JournalEntryController::class, 'index'])->name('journal-entries');
    Route::get('/journal-entries/create', [App\Http\Controllers\Accounting\JournalEntryController::class, 'create'])->name('journal-entries.create');
    Route::post('/journal-entries', [App\Http\Controllers\Accounting\JournalEntryController::class, 'store'])->name('journal-entries.store');
    Route::get('/journal-entries/{journalEntry}/edit', [App\Http\Controllers\Accounting\JournalEntryController::class, 'edit'])->name('journal-entries.edit');
    Route::put('/journal-entries/{journalEntry}', [App\Http\Controllers\Accounting\JournalEntryController::class, 'update'])->name('journal-entries.update');
    Route::delete('/journal-entries/{journalEntry}', [App\Http\Controllers\Accounting\JournalEntryController::class, 'destroy'])->name('journal-entries.destroy');

    // Payment Vouchers
    Route::resource('payment-vouchers', App\Http\Controllers\Accounting\PaymentVoucherController::class);
    Route::get('/payment-vouchers/{paymentVoucher}/download-attachment', [App\Http\Controllers\Accounting\PaymentVoucherController::class, 'downloadAttachment'])->name('payment-vouchers.download-attachment');
    Route::delete('/payment-vouchers/{paymentVoucher}/remove-attachment', [App\Http\Controllers\Accounting\PaymentVoucherController::class, 'removeAttachment'])->name('payment-vouchers.remove-attachment');

    // Receipt Vouchers
    Route::get('/receipt-vouchers', [App\Http\Controllers\Accounting\ReceiptVoucherController::class, 'index'])->name('receipt-vouchers.index');
    Route::get('/receipt-vouchers/create', [App\Http\Controllers\Accounting\ReceiptVoucherController::class, 'create'])->name('receipt-vouchers.create');
    Route::post('/receipt-vouchers', [App\Http\Controllers\Accounting\ReceiptVoucherController::class, 'store'])->name('receipt-vouchers.store');
    Route::get('/receipt-vouchers/{receiptVoucher}', [App\Http\Controllers\Accounting\ReceiptVoucherController::class, 'show'])->name('receipt-vouchers.show');
    Route::get('/receipt-vouchers/{receiptVoucher}/edit', [App\Http\Controllers\Accounting\ReceiptVoucherController::class, 'edit'])->name('receipt-vouchers.edit');
    Route::put('/receipt-vouchers/{receiptVoucher}', [App\Http\Controllers\Accounting\ReceiptVoucherController::class, 'update'])->name('receipt-vouchers.update');
    Route::delete('/receipt-vouchers/{receiptVoucher}', [App\Http\Controllers\Accounting\ReceiptVoucherController::class, 'destroy'])->name('receipt-vouchers.destroy');

    // Bank Accounts
    Route::get('/bank-accounts', [App\Http\Controllers\Accounting\BankAccountController::class, 'index'])->name('bank-accounts');
    Route::get('/bank-accounts/create', [App\Http\Controllers\Accounting\BankAccountController::class, 'create'])->name('bank-accounts.create');
    Route::post('/bank-accounts', [App\Http\Controllers\Accounting\BankAccountController::class, 'store'])->name('bank-accounts.store');
    Route::get('/bank-accounts/{bankAccount}', [App\Http\Controllers\Accounting\BankAccountController::class, 'show'])->name('bank-accounts.show');
    Route::get('/bank-accounts/{bankAccount}/edit', [App\Http\Controllers\Accounting\BankAccountController::class, 'edit'])->name('bank-accounts.edit');
    Route::put('/bank-accounts/{bankAccount}', [App\Http\Controllers\Accounting\BankAccountController::class, 'update'])->name('bank-accounts.update');
    Route::delete('/bank-accounts/{bankAccount}', [App\Http\Controllers\Accounting\BankAccountController::class, 'destroy'])->name('bank-accounts.destroy');

    // Bank Transfers
    Route::get('/bank-transfers', [App\Http\Controllers\Accounting\BankTransferController::class, 'index'])->name('bank-transfers');
    Route::get('/bank-transfers/create', [App\Http\Controllers\Accounting\BankTransferController::class, 'create'])->name('bank-transfers.create');
    Route::post('/bank-transfers', [App\Http\Controllers\Accounting\BankTransferController::class, 'store'])->name('bank-transfers.store');
    Route::get('/bank-transfers/{bankTransfer}/edit', [App\Http\Controllers\Accounting\BankTransferController::class, 'edit'])->name('bank-transfers.edit');
    Route::put('/bank-transfers/{bankTransfer}', [App\Http\Controllers\Accounting\BankTransferController::class, 'update'])->name('bank-transfers.update');
    Route::delete('/bank-transfers/{bankTransfer}', [App\Http\Controllers\Accounting\BankTransferController::class, 'destroy'])->name('bank-transfers.destroy');

    // Bank Reconciliation
    Route::get('/bank-reconciliation', [App\Http\Controllers\Accounting\BankReconciliationController::class, 'index'])->name('bank-reconciliation');
    Route::get('/bank-reconciliation/create', [App\Http\Controllers\Accounting\BankReconciliationController::class, 'create'])->name('bank-reconciliation.create');
    Route::post('/bank-reconciliation', [App\Http\Controllers\Accounting\BankReconciliationController::class, 'store'])->name('bank-reconciliation.store');
    Route::get('/bank-reconciliation/{bankReconciliation}/edit', [App\Http\Controllers\Accounting\BankReconciliationController::class, 'edit'])->name('bank-reconciliation.edit');
    Route::put('/bank-reconciliation/{bankReconciliation}', [App\Http\Controllers\Accounting\BankReconciliationController::class, 'update'])->name('bank-reconciliation.update');
    Route::delete('/bank-reconciliation/{bankReconciliation}', [App\Http\Controllers\Accounting\BankReconciliationController::class, 'destroy'])->name('bank-reconciliation.destroy');

    // Bill Purchases
    Route::get('/bill-purchases', [App\Http\Controllers\Accounting\BillPurchaseController::class, 'index'])->name('bill-purchases');
    Route::get('/bill-purchases/create', [App\Http\Controllers\Accounting\BillPurchaseController::class, 'create'])->name('bill-purchases.create');
    Route::post('/bill-purchases', [App\Http\Controllers\Accounting\BillPurchaseController::class, 'store'])->name('bill-purchases.store');
    Route::get('/bill-purchases/{billPurchase}/edit', [App\Http\Controllers\Accounting\BillPurchaseController::class, 'edit'])->name('bill-purchases.edit');
    Route::put('/bill-purchases/{billPurchase}', [App\Http\Controllers\Accounting\BillPurchaseController::class, 'update'])->name('bill-purchases.update');
    Route::delete('/bill-purchases/{billPurchase}', [App\Http\Controllers\Accounting\BillPurchaseController::class, 'destroy'])->name('bill-purchases.destroy');

    // Budget
    Route::get('/budgets', [App\Http\Controllers\Accounting\BudgetController::class, 'index'])->name('budgets.index');
    Route::get('/budgets/create', [App\Http\Controllers\Accounting\BudgetController::class, 'create'])->name('budgets.create');
    Route::post('/budgets', [App\Http\Controllers\Accounting\BudgetController::class, 'store'])->name('budgets.store');
    Route::get('/budgets/{budget}', [App\Http\Controllers\Accounting\BudgetController::class, 'show'])->name('budgets.show');
    Route::get('/budgets/{budget}/edit', [App\Http\Controllers\Accounting\BudgetController::class, 'edit'])->name('budgets.edit');
    Route::put('/budgets/{budget}', [App\Http\Controllers\Accounting\BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('/budgets/{budget}', [App\Http\Controllers\Accounting\BudgetController::class, 'destroy'])->name('budgets.destroy');

    // Fees
    Route::get('/fees', [App\Http\Controllers\Accounting\FeeController::class, 'index'])->name('fees.index');
    Route::get('/fees/create', [App\Http\Controllers\Accounting\FeeController::class, 'create'])->name('fees.create');
    Route::post('/fees', [App\Http\Controllers\Accounting\FeeController::class, 'store'])->name('fees.store');
    Route::get('/fees/{fee}', [App\Http\Controllers\Accounting\FeeController::class, 'show'])->name('fees.show');
    Route::get('/fees/{fee}/edit', [App\Http\Controllers\Accounting\FeeController::class, 'edit'])->name('fees.edit');
    Route::put('/fees/{fee}', [App\Http\Controllers\Accounting\FeeController::class, 'update'])->name('fees.update');
    Route::patch('/fees/{fee}/status', [App\Http\Controllers\Accounting\FeeController::class, 'changeStatus'])->name('fees.changeStatus');
    Route::delete('/fees/{fee}', [App\Http\Controllers\Accounting\FeeController::class, 'destroy'])->name('fees.destroy');

    // Penalties
    Route::get('/penalties', [App\Http\Controllers\Accounting\PenaltyController::class, 'index'])->name('penalties.index');
    Route::get('/penalties/create', [App\Http\Controllers\Accounting\PenaltyController::class, 'create'])->name('penalties.create');
    Route::post('/penalties', [App\Http\Controllers\Accounting\PenaltyController::class, 'store'])->name('penalties.store');
    Route::get('/penalties/{penalty}', [App\Http\Controllers\Accounting\PenaltyController::class, 'show'])->name('penalties.show');
    Route::get('/penalties/{penalty}/edit', [App\Http\Controllers\Accounting\PenaltyController::class, 'edit'])->name('penalties.edit');
    Route::put('/penalties/{penalty}', [App\Http\Controllers\Accounting\PenaltyController::class, 'update'])->name('penalties.update');
    Route::patch('/penalties/{penalty}/status', [App\Http\Controllers\Accounting\PenaltyController::class, 'changeStatus'])->name('penalties.changeStatus');
    Route::delete('/penalties/{penalty}', [App\Http\Controllers\Accounting\PenaltyController::class, 'destroy'])->name('penalties.destroy');

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/other-income', [App\Http\Controllers\Accounting\Reports\OtherIncomeReportController::class, 'index'])->name('other-income');
        Route::get('/trial-balance', [App\Http\Controllers\Accounting\Reports\TrialBalanceReportController::class, 'index'])->name('trial-balance');
        Route::get('/income-statement', [App\Http\Controllers\Accounting\Reports\IncomeStatementReportController::class, 'index'])->name('income-statement');
        Route::get('/balance-sheet', [App\Http\Controllers\Accounting\Reports\BalanceSheetReportController::class, 'index'])->name('balance-sheet');
        Route::get('/cash-book', [App\Http\Controllers\Accounting\Reports\CashBookReportController::class, 'index'])->name('cash-book');
        Route::get('/cash-flow', [App\Http\Controllers\Accounting\Reports\CashFlowReportController::class, 'index'])->name('cash-flow');
        Route::get('/general-ledger', [App\Http\Controllers\Accounting\Reports\GeneralLedgerReportController::class, 'index'])->name('general-ledger');
        Route::get('/expenses-summary', [App\Http\Controllers\Accounting\Reports\ExpensesSummaryReportController::class, 'index'])->name('expenses-summary');
        Route::get('/accounting-notes', [App\Http\Controllers\Accounting\Reports\AccountingNotesReportController::class, 'index'])->name('accounting-notes');
        Route::get('/changes-equity', [App\Http\Controllers\Accounting\Reports\ChangesEquityReportController::class, 'index'])->name('changes-equity');
    });
});

////////////////////////////////////////////// END ACCOUNTING MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// CUSTOMER MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
});

////////////////////////////////////////////// END CUSTOMER MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// CASHCOLLATERALS MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->prefix('cash_collaterals')->group(function () {
    Route::get('cash_collaterals', [CashCollateralController::class, 'index'])->name('cash_collaterals.index');
    Route::get('cash_collaterals/create', [CashCollateralController::class, 'create'])->name('cash_collaterals.create');
    Route::get('cash_collaterals/deposit', [CashCollateralController::class, 'create'])->name('cash_collaterals.deposit');
    Route::get('cash_collaterals/withdraw', [CashCollateralController::class, 'create'])->name('cash_collaterals.withdraw');
    Route::post('cash_collaterals', [CashCollateralController::class, 'store'])->name('cash_collaterals.store');
    Route::get('cash_collaterals/{cashcollateral}', [CashCollateralController::class, 'show'])->name('cash_collaterals.show');
    Route::get('cash_collaterals/{cashcollateral}/edit', [CashCollateralController::class, 'edit'])->name('cash_collaterals.edit');
    Route::put('cash_collaterals/{cashcollateral}', [CashCollateralController::class, 'update'])->name('cash_collaterals.update');
    Route::delete('cash_collaterals/{cashcollateral}', [CashCollateralController::class, 'destroy'])->name('cash_collaterals.destroy');
});

////////////////////////////////////////////// END CASHCOLLATERALS  MANAGEMENT ///////////////////////////////////////////

Route::get('/get-districts/{regionId}', [LocationController::class, 'getDistricts']);




Route::post('/logout', function () {
    Auth::logout();
    return redirect('/')->with('success', 'You are successfully logout.');
})->middleware('auth');


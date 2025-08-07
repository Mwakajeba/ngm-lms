<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Define microfinance-specific permissions
        $permissions = [
            // User & Staff Management
            'view users',
            'create user',
            'edit user',
            'delete user',
            'assign roles',
            'view user profile',
            'change user status',
            'manage staff',

            // Company & Branch Management
            'view companies',
            'create company',
            'edit company',
            'delete company',
            'manage company settings',
            'view branches',
            'create branch',
            'edit branch',
            'delete branch',
            'assign users to branches',

            // Client Management
            'view clients',
            'create client',
            'edit client',
            'delete client',
            'view client profile',
            'manage client documents',
            'view client history',
            'approve client registration',

            // Loan Management
            'view loans',
            'create loan',
            'view checked loans',
            'view applied loans',
            'view approved loans',
            'view authorized loans',
            'view defaulted loans',
            'view rejected loans',
            'edit loan',
            'remove guarantor',
            'default loan',
            'delete loan',
            'approve loan',
            'reject loan',
            'disburse loan',
            'create loan product',
            'view product details',
            'edit loan product',
            'delete loan product',
            'view loan product',
            'deactivate loan product',
            'view loan details',
            'manage loan documents',
            'view loan documents',
            'calculate loan interest',
            'generate loan schedule',
            'process loan payments',
            'manage loan fees',
            'view loan history', 
            'manage loan products',

            // Borrower Management
            'view borrowers',
            'create borrower',
            'edit borrower',
            'delete borrower',
            'view borrower profile',
            'manage borrower documents',
            'check borrower eligibility',
            'view borrower loans',
            'manage borrower groups',

            // Collections & Payments
            'view collections',
            'create collection',
            'edit collection',
            'delete collection',
            'process payments',
            'record cash payments',
            'record bank transfers',
            'manage payment schedules',
            'view payment history',
            'generate receipts',
            'manage late payments',
            'process penalties',

            // Accounting & Financial
            'view accounting',
            'create journal entries',
            'edit journal entries',
            'delete journal entries',
            'view chart of accounts',
            'manage chart of accounts',
            'view bank accounts',
            'manage bank accounts',
            'view bank reconciliation',
            'perform bank reconciliation',
            'view general ledger',
            'manage financial year',
            'close accounting period',

            // Savings & Deposits
            'view savings accounts',
            'create savings account',
            'edit savings account',
            'delete savings account',
            'process deposits',
            'process withdrawals',
            'calculate interest on savings',
            'manage savings fees',
            'view savings history',

            // Reports & Analytics
            'view reports',
            'generate reports',
            'export reports',
            'view loan portfolio report',
            'view collection report',
            'view delinquency report',
            'view financial statements',
            'view FINANCIAL REPORT SUMMARY',
            'view client reports',
            'view branch performance',
            'view staff performance',
            'view audit reports',
            'view compliance reports',

            // Risk Management
            'view risk assessment',
            'create risk assessment',
            'edit risk assessment',
            'manage loan limits',
            'view credit scores',
            'manage collateral',
            'view insurance policies',
            'manage loan guarantees',

            // Settings & Configuration
            'view settings',
            'edit settings',
            'manage system settings',
            'view system configurations',
            'edit system configurations',
            'manage system configurations',
            'view system config',
            'edit system config',
            'manage system config',
            'manage interest rates',
            'manage fee setting',
            'manage role & permission',
            'mange penalty setting',
            'manage payment terms',
            'view backup settings',
            'create backup',
            'restore backup',
            'delete backup',
            'manage user setting',
            'manage branch setting',
            'manage campany setting',
            'delete role',
            'edit role',
            'view role',
            'create role',
            'create permission',
            'view charges',


            // AI Assistant
            'use AI assistant',
            'view AI assistant',

            // Dashboard & Analytics
            'view dashboard',
            'view analytics',
            'view statistics',
            'view kpi reports',

            // Menu Management
            'view menus',
            'manage menus',
            'assign menu permissions',

            //bank accounts
            'view bank accounts',
            'create bank account',
            'edit bank account',
            'delete bank account',
            'view bank account details',
            'manage bank account transactions',

            ////CASH COLLATERAL PERMISSION////
            'delete transaction',
            'edit transaction',
            'deposit cash collateral',
            'withdraw cash collateral',
            'edit cash collateral',
            'delete cash collateral',
            'print cash collateral transations',
            'view cash collaterals',
            'create cash collateral',

            ///group permission

            'view groups',
            'create group',
            'delete group',
            'edit group',
            'view group details',
        ];

        // Create or update permissions
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
        }

        // Create system roles with their permissions
        $this->createSystemRoles();

        // Create custom roles
        $this->createCustomRoles();

        // Assign admin role to user with ID 1 (if exists)
        $user = User::find(1);
        if ($user && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }

    private function createSystemRoles()
    {
        // Super Admin Role - All permissions
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web'
        ]);
        $superAdminRole->description = 'Full system access with all microfinance permissions';
        $superAdminRole->save();
        $superAdminRole->syncPermissions(Permission::all());

        // Admin Role - Company level admin
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);
        $adminRole->description = 'Microfinance company administrator with full access';
        $adminRole->save();

        $adminPermissions = [
            'view users',
            'create user',
            'edit user',
            'delete user',
            'assign roles',
            'view user profile',
            'change user status',
            'manage staff',
            'view companies',
            'view charges',
            'view dashboard',
            'edit company',
            'manage company settings',
            'view branches',
            'create branch',
            'edit branch',
            'delete branch',
            'assign users to branches',
            'view clients',
            'create client',
            'edit client',
            'delete client',
            'view client profile',
            'manage client documents',
            'view client history',
            'approve client registration',
            'view loans',
            'create loan',
            'edit loan',
            'delete loan',
            'approve loan',
            'reject loan',
            'disburse loan',
            'view loan details',
            'manage loan documents',
            'calculate loan interest',
            'generate loan schedule',
            'process loan payments',
            'manage loan fees',
            'view loan history',
            'view borrowers',
            'create borrower',
            'edit borrower',
            'delete borrower',
            'view borrower profile',
            'manage borrower documents',
            'check borrower eligibility',
            'view borrower loans',
            'manage borrower groups',
            'view collections',
            'create collection',
            'edit collection',
            'delete collection',
            'process payments',
            'record cash payments',
            'record bank transfers',
            'manage payment schedules',
            'view payment history',
            'generate receipts',
            'manage late payments',
            'process penalties',
            'view accounting',
            'create journal entries',
            'edit journal entries',
            'delete journal entries',
            'view chart of accounts',
            'manage chart of accounts',
            'view bank accounts',
            'manage bank accounts',
            'view bank reconciliation',
            'perform bank reconciliation',
            'view general ledger',
            'manage financial year',
            'close accounting period',
            'view savings accounts',
            'create savings account',
            'edit savings account',
            'delete savings account',
            'process deposits',
            'process withdrawals',
            'calculate interest on savings',
            'manage savings fees',
            'view savings history',
            'view reports',
            'generate reports',
            'export reports',
            'view loan portfolio report',
            'view collection report',
            'view delinquency report',
            'view financial statements',
            'view FINANCIAL REPORT SUMMARY',
            'view client reports',
            'view branch performance',
            'view staff performance',
            'view audit reports',
            'view compliance reports',
            'view risk assessment',
            'create risk assessment',
            'edit risk assessment',
            'manage loan limits',
            'view credit scores',
            'manage collateral',
            'view insurance policies',
            'manage loan guarantees',
            'view settings',
            'edit settings',
            'manage system settings',
            'view system configurations',
            'edit system configurations',
            'manage system configurations',
            'view system config',
            'edit system config',
            'manage system config',
            'manage loan products',
            'manage interest rates',
            'manage payment terms',
            'view backup settings',
            'create backup',
            'restore backup',
            'delete backup',
            'use AI assistant',
            'view AI assistant',
            'view dashboard',
            'view analytics',
            'view statistics',
            'view kpi reports',
            'view menus',
            'manage menus',
            'assign menu permissions'
        ];
        $adminRole->syncPermissions($adminPermissions);

        // Manager Role - Branch level management
        $managerRole = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web'
        ]);
        $managerRole->description = 'Branch manager with operational microfinance access';
        $managerRole->save();

        $managerPermissions = [
            'view users',
            'create user',
            'edit user',
            'view user profile',
            'manage staff',
            'view branches',
            'edit branch',
            'view clients',
            'create client',
            'edit client',
            'view client profile',
            'manage client documents',
            'view client history',
            'approve client registration',
            'view loans',
            'create loan',
            'edit loan',
            'approve loan',
            'reject loan',
            'disburse loan',
            'view loan details',
            'manage loan documents',
            'calculate loan interest',
            'generate loan schedule',
            'process loan payments',
            'manage loan fees',
            'view loan history',
            'view borrowers',
            'create borrower',
            'edit borrower',
            'view borrower profile',
            'manage borrower documents',
            'check borrower eligibility',
            'view borrower loans',
            'manage borrower groups',
            'view collections',
            'create collection',
            'edit collection',
            'process payments',
            'record cash payments',
            'record bank transfers',
            'manage payment schedules',
            'view payment history',
            'generate receipts',
            'manage late payments',
            'process penalties',
            'view accounting',
            'create journal entries',
            'edit journal entries',
            'view chart of accounts',
            'view bank accounts',
            'view bank reconciliation',
            'view general ledger',
            'view savings accounts',
            'create savings account',
            'edit savings account',
            'process deposits',
            'process withdrawals',
            'view savings history',
            'view reports',
            'generate reports',
            'export reports',
            'view loan portfolio report',
            'view collection report',
            'view delinquency report',
            'view financial statements',
            'view FINANCIAL REPORT SUMMARY',
            'view client reports',
            'view branch performance',
            'view staff performance',
            'view risk assessment',
            'create risk assessment',
            'edit risk assessment',
            'view credit scores',
            'manage collateral',
            'view settings',
            'view backup settings',
            'create backup',
            'use AI assistant',
            'view AI assistant',
            'view dashboard',
            'view analytics',
            'view statistics',
            'view kpi reports',
            'view menus'
        ];
        $managerRole->syncPermissions($managerPermissions);

        // User Role - Standard user
        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web'
        ]);
        $userRole->description = 'Standard microfinance user with basic access';
        $userRole->save();

        $userPermissions = [
            'view users',
            'view user profile',
            'view branches',
            'view clients',
            'view client profile',
            'view client history',
            'view loans',
            'view loan details',
            'view loan history',
            'view borrowers',
            'view borrower profile',
            'view borrower loans',
            'view collections',
            'view payment history',
            'view accounting',
            'create journal entries',
            'view chart of accounts',
            'view bank accounts',
            'view savings accounts',
            'view savings history',
            'view settings',
            'use AI assistant',
            'view AI assistant',
            'view reports',
            'view loan portfolio report',
            'view collection report',
            'view client reports',
            'view dashboard',
            'view statistics',
            'view menus'
        ];
        $userRole->syncPermissions($userPermissions);

        // Viewer Role - Read-only access
        $viewerRole = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'web'
        ]);
        $viewerRole->description = 'Read-only access to microfinance data';
        $viewerRole->save();

        $viewerPermissions = [
            'view users',
            'view user profile',
            'view branches',
            'view clients',
            'view client profile',
            'view client history',
            'view loans',
            'view loan details',
            'view loan history',
            'view borrowers',
            'view borrower profile',
            'view borrower loans',
            'view collections',
            'view payment history',
            'view accounting',
            'view chart of accounts',
            'view bank accounts',
            'view savings accounts',
            'view savings history',
            'view settings',
            'view AI assistant',
            'view reports',
            'view loan portfolio report',
            'view collection report',
            'view client reports',
            'view dashboard',
            'view statistics',
            'view menus'
        ];
        $viewerRole->syncPermissions($viewerPermissions);
    }

    private function createCustomRoles()
    {
        // Loan Officer Role
        $loanOfficerRole = Role::firstOrCreate([
            'name' => 'loan-officer',
            'guard_name' => 'web'
        ]);
        $loanOfficerRole->description = 'Loan officer with client and loan management focus';
        $loanOfficerRole->save();

        $loanOfficerPermissions = [
            'view users',
            'view user profile',
            'view branches',
            'view clients',
            'create client',
            'edit client',
            'view client profile',
            'manage client documents',
            'view client history',
            'view loans',
            'create loan',
            'edit loan',
            'view loan details',
            'manage loan documents',
            'calculate loan interest',
            'generate loan schedule',
            'view loan history',
            'view borrowers',
            'create borrower',
            'edit borrower',
            'view borrower profile',
            'manage borrower documents',
            'check borrower eligibility',
            'view borrower loans',
            'manage borrower groups',
            'view collections',
            'view payment history',
            'view accounting',
            'create journal entries',
            'view chart of accounts',
            'view bank accounts',
            'view reports',
            'view loan portfolio report',
            'view collection report',
            'view client reports',
            'view risk assessment',
            'create risk assessment',
            'edit risk assessment',
            'view credit scores',
            'manage collateral',
            'view settings',
            'use AI assistant',
            'view AI assistant',
            'view dashboard',
            'view statistics',
            'view menus'
        ];
        $loanOfficerRole->syncPermissions($loanOfficerPermissions);

        // Accountant Role
        $accountantRole = Role::firstOrCreate([
            'name' => 'accountant',
            'guard_name' => 'web'
        ]);
        $accountantRole->description = 'Financial specialist with accounting and reporting focus';
        $accountantRole->save();

        $accountantPermissions = [
            'view users',
            'view user profile',
            'view branches',
            'view clients',
            'view client profile',
            'view client history',
            'view loans',
            'view loan details',
            'view loan history',
            'view borrowers',
            'view borrower profile',
            'view borrower loans',
            'view collections',
            'create collection',
            'edit collection',
            'process payments',
            'record cash payments',
            'record bank transfers',
            'manage payment schedules',
            'view payment history',
            'generate receipts',
            'manage late payments',
            'process penalties',
            'view accounting',
            'create journal entries',
            'edit journal entries',
            'delete journal entries',
            'view chart of accounts',
            'manage chart of accounts',
            'view bank accounts',
            'manage bank accounts',
            'view bank reconciliation',
            'perform bank reconciliation',
            'view general ledger',
            'manage financial year',
            'close accounting period',
            'view savings accounts',
            'create savings account',
            'edit savings account',
            'process deposits',
            'process withdrawals',
            'calculate interest on savings',
            'manage savings fees',
            'view savings history',
            'view reports',
            'generate reports',
            'export reports',
            'view loan portfolio report',
            'view collection report',
            'view delinquency report',
            'view financial statements',
            'view client reports',
            'view audit reports',
            'view compliance reports',
            'view settings',
            'use AI assistant',
            'view AI assistant',
            'view dashboard',
            'view analytics',
            'view statistics',
            'view kpi reports',
            'view menus'
        ];
        $accountantRole->syncPermissions($accountantPermissions);

        // Cashier Role
        $cashierRole = Role::firstOrCreate([
            'name' => 'cashier',
            'guard_name' => 'web'
        ]);
        $cashierRole->description = 'Cash handling and payment processing specialist';
        $cashierRole->save();

        $cashierPermissions = [
            'view users',
            'view user profile',
            'view branches',
            'view clients',
            'view client profile',
            'view loans',
            'view loan details',
            'view borrowers',
            'view borrower profile',
            'view collections',
            'create collection',
            'edit collection',
            'process payments',
            'record cash payments',
            'record bank transfers',
            'view payment history',
            'generate receipts',
            'view accounting',
            'create journal entries',
            'view chart of accounts',
            'view bank accounts',
            'view savings accounts',
            'process deposits',
            'process withdrawals',
            'view savings history',
            'view settings',
            'use AI assistant',
            'view AI assistant',
            'view reports',
            'view collection report',
            'view dashboard',
            'view statistics',
            'view menus'
        ];
        $cashierRole->syncPermissions($cashierPermissions);

        // Collection Officer Role
        $collectionOfficerRole = Role::firstOrCreate([
            'name' => 'collection-officer',
            'guard_name' => 'web'
        ]);
        $collectionOfficerRole->description = 'Specialized in loan collections and payment management';
        $collectionOfficerRole->save();

        $collectionOfficerPermissions = [
            'view users',
            'view user profile',
            'view branches',
            'view clients',
            'view client profile',
            'view client history',
            'view loans',
            'view loan details',
            'view loan history',
            'view borrowers',
            'view borrower profile',
            'view borrower loans',
            'view collections',
            'create collection',
            'edit collection',
            'process payments',
            'record cash payments',
            'record bank transfers',
            'manage payment schedules',
            'view payment history',
            'generate receipts',
            'manage late payments',
            'process penalties',
            'view accounting',
            'create journal entries',
            'view chart of accounts',
            'view bank accounts',
            'view reports',
            'view collection report',
            'view delinquency report',
            'view client reports',
            'view settings',
            'use AI assistant',
            'view AI assistant',
            'view dashboard',
            'view statistics',
            'view menus'
        ];
        $collectionOfficerRole->syncPermissions($collectionOfficerPermissions);

        // Risk Officer Role
        $riskOfficerRole = Role::firstOrCreate([
            'name' => 'risk-officer',
            'guard_name' => 'web'
        ]);
        $riskOfficerRole->description = 'Risk assessment and credit analysis specialist';
        $riskOfficerRole->save();

        $riskOfficerPermissions = [
            'view users',
            'view user profile',
            'view branches',
            'view clients',
            'view client profile',
            'view client history',
            'view loans',
            'view loan details',
            'view loan history',
            'view borrowers',
            'view borrower profile',
            'view borrower loans',
            'view collections',
            'view payment history',
            'view accounting',
            'view chart of accounts',
            'view bank accounts',
            'view reports',
            'view loan portfolio report',
            'view delinquency report',
            'view client reports',
            'view compliance reports',
            'view risk assessment',
            'create risk assessment',
            'edit risk assessment',
            'manage loan limits',
            'view credit scores',
            'manage collateral',
            'view insurance policies',
            'manage loan guarantees',
            'view settings',
            'use AI assistant',
            'view AI assistant',
            'view dashboard',
            'view analytics',
            'view statistics',
            'view menus'
        ];
        $riskOfficerRole->syncPermissions($riskOfficerPermissions);
    }
}

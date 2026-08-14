<?php

namespace Database\Seeders;

use App\Models\AccountClass;
use App\Models\AccountClassGroup;
use App\Models\ChartAccount;
use App\Models\Company;
use App\Models\Hr\PayrollChartAccount;
use Illuminate\Database\Seeder;

class PayrollChartAccountSeeder extends Seeder
{
    /**
     * Create missing payroll GL accounts (if needed) and map them
     * onto each company's payroll chart account settings.
     */
    public function run(): void
    {
        $accountMappings = [
            'salary_advance_receivable_account_id' => ['1103', 'Staff Advances / Receivables', 'receivable'],
            'salary_payable_account_id' => ['2103', 'Net Salary Payable', 'payable'],
            'salary_expense_account_id' => ['5101', 'Salaries and Wages', 'operating_expense'],
            'allowance_expense_account_id' => ['5146', 'Allowances (Transport, Housing, etc.)', 'operating_expense'],
            'heslb_payable_account_id' => ['2670', 'HESLB Payable', 'payable'],
            'pension_expense_account_id' => ['5226', 'Social Security Costs', 'admin_expense'],
            'pension_payable_account_id' => ['2109', 'Social Security Payable', 'payable'],
            'payee_payable_account_id' => ['2125', 'PAYE Payable', 'payable'],
            'insurance_expense_account_id' => ['5466', 'NHIF / Insurance Expenses', 'finance_expense'],
            'insurance_payable_account_id' => ['2123', 'NHIF Payable', 'payable'],
            'wcf_expense_account_id' => ['5122', 'WCF Contribution Cost', 'operating_expense'],
            'wcf_payable_account_id' => ['2146', 'WCF Payable', 'payable'],
            'sdl_expense_account_id' => ['5124', 'SDL Expenses', 'operating_expense'],
            'sdl_payable_account_id' => ['2120', 'SDL Payable', 'payable'],
            'trade_union_payable_account_id' => ['2333', 'Trade Union Payable', 'payable'],
            'other_payable_account_id' => ['2102', 'Other Accrued Liabilities', 'payable'],
        ];

        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->warn('No companies found. Please seed companies first.');
            return;
        }

        foreach ($companies as $company) {
            $this->command->info("Seeding payroll chart accounts for: {$company->name} (ID: {$company->id})");

            $groups = [
                'receivable' => $this->resolveGroup($company->id, '1300', 'Other Receivables', 'Assets'),
                'payable' => $this->resolveGroup($company->id, '2100', 'Other payables', 'Liabilities'),
                'operating_expense' => $this->resolveGroup($company->id, '5100', 'Operating Expenses', 'Expenses'),
                'admin_expense' => $this->resolveGroup($company->id, '5200', 'Administrative Expenses', 'Expenses'),
                'finance_expense' => $this->resolveGroup($company->id, '5400', 'Finance Expenses', 'Expenses'),
            ];

            $updateData = [];

            foreach ($accountMappings as $field => $accountInfo) {
                [$accountCode, $accountName, $groupKey] = $accountInfo;
                $group = $groups[$groupKey];

                if (! $group) {
                    $this->command->warn("  Skipping {$accountCode} — no {$groupKey} group for company {$company->id}");
                    continue;
                }

                $account = $this->resolveOrCreateAccount($accountCode, $accountName, $group, $field);

                $updateData[$field] = $account->id;
            }

            $payrollChartAccount = PayrollChartAccount::firstOrCreate(
                ['company_id' => $company->id],
                []
            );

            $payrollChartAccount->update($updateData);

            $this->command->info("Updated payroll chart account settings for company: {$company->name} (ID: {$company->id})");
        }

        $this->command->info('Payroll chart account seeding completed successfully!');
    }

    protected function resolveOrCreateAccount(string $accountCode, string $accountName, AccountClassGroup $group, string $field): ChartAccount
    {
        $account = ChartAccount::where('account_code', $accountCode)->first();

        // 5101 is already used as Direct Write Off in this system — do not reuse it for salaries.
        if ($field === 'salary_expense_account_id' && $account && ! $this->nameLooksLikeSalary($account->account_name)) {
            $this->command->warn("  {$accountCode} exists as '{$account->account_name}' — creating 5110 for salaries instead");
            $accountCode = '5110';
            $account = ChartAccount::where('account_code', $accountCode)->first();
        }

        if ($account) {
            $this->command->info("  Found account: {$account->account_code} - {$account->account_name} (ID: {$account->id})");

            return $account;
        }

        $account = ChartAccount::create([
            'account_class_group_id' => $group->id,
            'account_code' => $accountCode,
            'account_name' => $accountName,
            'has_cash_flow' => true,
            'has_equity' => false,
            'cash_flow_category_id' => 1,
            'equity_category_id' => null,
        ]);

        $this->command->info("  Created account: {$account->account_code} - {$account->account_name} (ID: {$account->id})");

        return $account;
    }

    protected function nameLooksLikeSalary(string $name): bool
    {
        $name = strtolower($name);

        return str_contains($name, 'salar') || str_contains($name, 'wage');
    }

    protected function resolveGroup(int $companyId, string $groupCode, string $groupName, string $className): ?AccountClassGroup
    {
        $group = AccountClassGroup::where('company_id', $companyId)
            ->where('group_code', $groupCode)
            ->first();

        if ($group) {
            return $group;
        }

        $group = AccountClassGroup::where('company_id', $companyId)
            ->where('name', 'like', '%'.$groupName.'%')
            ->first();

        if ($group) {
            return $group;
        }

        $class = AccountClass::where('name', $className)->first();

        if (! $class) {
            $this->command->warn("  Account class '{$className}' not found");

            return null;
        }

        $group = AccountClassGroup::create([
            'class_id' => $class->id,
            'company_id' => $companyId,
            'group_code' => $groupCode,
            'name' => $groupName,
        ]);

        $this->command->info("  Created account group: {$groupCode} - {$groupName} (ID: {$group->id})");

        return $group;
    }
}

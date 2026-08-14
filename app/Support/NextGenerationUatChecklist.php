<?php

namespace App\Support;

/**
 * UAT checklist content for NEXTGENERATION MICROFINANCE (NGML).
 */
final class NextGenerationUatChecklist
{
    /**
     * @return array{company: array{name: string, email: string, phone: string, logo_public: string}, pricing: array{system: int, integrations: int, total: int}, sections: list<array{title: string, rows: list<array{id: string, feature: string, detail: string, status: ?string, comment: ?string}>}>}
     */
    public static function data(): array
    {
        return [
            'company' => [
                'name' => 'NEXTGENERATION MICROFINANCE',
                'email' => 'info@ngml.co.tz',
                'phone' => '0767332093',
                'logo_public' => 'assets/images/icons/smartfinance.png',
            ],
            'pricing' => [
                'system' => 2_500_000,
                'integrations' => 1_700_000,
                'total' => 4_200_000,
            ],
            'sections' => [
                [
                    'title' => '1. Authentication, access & session',
                    'rows' => [
                        [
                            'id' => 'SEC-01',
                            'feature' => 'User login',
                            'detail' => 'Valid user can log in with correct credentials; invalid password is rejected with a clear message. Session persists as expected across navigation. Logout clears the session and prevents back-button access to protected pages.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'SEC-02',
                            'feature' => 'Password recovery',
                            'detail' => 'Forgot-password flow sends OTP or reset link as configured. User can set a new password and log in with it. Expired or reused tokens are rejected.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'SEC-03',
                            'feature' => 'Branch switching',
                            'detail' => 'Users assigned to multiple branches can switch active branch where enabled. Data scope (loans, reports) respects the selected branch according to business rules.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'SEC-04',
                            'feature' => 'Role-based permissions',
                            'detail' => 'Menus and actions hidden or disabled when the role lacks permission. Attempting direct URL access without permission returns 403 or redirect as designed.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'SEC-05',
                            'feature' => 'Subscription / access gate',
                            'detail' => 'If subscription is expired, restricted users see the subscription-expired experience and cannot reach core modules until renewal (per product rules).',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '2. Organisation — company, branches, staff',
                    'rows' => [
                        [
                            'id' => 'ORG-01',
                            'feature' => 'Company profile',
                            'detail' => 'Company name, contacts, and branding (logo) display correctly on dashboards and PDF reports. Updates to company settings persist after save.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'ORG-02',
                            'feature' => 'Branches',
                            'detail' => 'Branches can be created and edited (per permission). Users are assignable to one or more branches. Loan and report filters list only permitted branches.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'ORG-03',
                            'feature' => 'Users & roles',
                            'detail' => 'Admin can create users, assign roles, and assign branches. Deactivated users cannot log in. Role changes take effect without stale permission cache issues.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '3. Dashboard & analytics',
                    'rows' => [
                        [
                            'id' => 'DAS-01',
                            'feature' => 'Executive dashboard',
                            'detail' => 'KPI tiles and charts load without error for a typical dataset. Figures reconcile at high level with underlying loan/accounting data for a sample spot-check.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'DAS-02',
                            'feature' => 'Arrears / classification widgets',
                            'detail' => 'Loans-by-bucket or arrears widgets respect configured arrears classifications. DPD and principal-in-arrears match loan-detail logic for sampled loans.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'DAS-03',
                            'feature' => 'Loan analytics screen',
                            'detail' => 'Dedicated analytics views/API return KPIs and charts; filters work; export or drill-down behaves as documented.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '4. Customers',
                    'rows' => [
                        [
                            'id' => 'CUS-01',
                            'feature' => 'Customer registration',
                            'detail' => 'New customer can be captured with mandatory fields validated. Duplicates (e.g. phone/NID if enforced) are handled per policy.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'CUS-02',
                            'feature' => 'Customer profile & edit',
                            'detail' => 'Profile view shows contact, IDs, and relationships. Edits save correctly; audit or history shows changes where implemented.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'CUS-03',
                            'feature' => 'Documents & KYC',
                            'detail' => 'Required document types can be uploaded, viewed, and replaced. File size/type restrictions enforced. Download works for authorised users.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'CUS-04',
                            'feature' => 'Customer approval workflow',
                            'detail' => 'If registration approval is used, approver can approve/reject; customer status updates and downstream loan application rules respect approved state.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '5. Loan products & configuration',
                    'rows' => [
                        [
                            'id' => 'PRD-01',
                            'feature' => 'Loan product setup',
                            'detail' => 'Products define amount limits, tenor, interest method, fees, and repayment frequency correctly. Inactive products cannot be selected for new applications.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'PRD-02',
                            'feature' => 'Interest & schedule preview',
                            'detail' => 'Calculator or schedule preview matches disbursement schedule after loan creation for a test case (flat/reducing as applicable).',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'PRD-03',
                            'feature' => 'Fees, penalties & charges',
                            'detail' => 'Configured fees and penalties appear on schedule or transactions as designed; waivers (if any) follow approval rules.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '6. Loan lifecycle — application to disbursement',
                    'rows' => [
                        [
                            'id' => 'LN-01',
                            'feature' => 'Loan application capture',
                            'detail' => 'Application captures product, amount, tenor, purpose, guarantors/collateral as required. Validation prevents incomplete submit.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'LN-02',
                            'feature' => 'Approval / rejection',
                            'detail' => 'Workflow states (applied, approved, rejected) display correctly. Notifications or task lists (if any) align with state changes.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'LN-03',
                            'feature' => 'Disbursement',
                            'detail' => 'Disbursement posts correct principal, date, and branch. Loan status becomes active; schedule lines match approved terms.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'LN-04',
                            'feature' => 'Schedule integrity',
                            'detail' => 'Total scheduled principal equals disbursed amount (within rounding rules). Due dates follow product frequency; first instalment date correct.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'LN-05',
                            'feature' => 'Loan restructuring / top-up / write-off',
                            'detail' => 'If enabled: restructure or top-up recalculates schedule and balances; write-off requires authorisation and reflects in balances and reports.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '7. Repayments, arrears & penalties',
                    'rows' => [
                        [
                            'id' => 'REP-01',
                            'feature' => 'Record repayment',
                            'detail' => 'Payment allocates to principal, interest, fees per waterfall. Outstanding and schedule lines update; receipt reference stored.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'REP-02',
                            'feature' => 'Partial & advance payments',
                            'detail' => 'Partial payment applies correctly; advance handling (if supported) does not corrupt schedule or interest accrual.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'REP-03',
                            'feature' => 'Arrears calculation',
                            'detail' => 'Days in arrears and arrears amount on loan match manual calculation for sampled overdue loans (including first-overdue-instalment rule if used).',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'REP-04',
                            'feature' => 'Penalties on overdue lines',
                            'detail' => 'Penalties accrue or post per configuration; reversal or adjustment respects permissions.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '8. Groups, collateral & cash collateral',
                    'rows' => [
                        [
                            'id' => 'GRP-01',
                            'feature' => 'Group & members',
                            'detail' => 'Group created; members added/removed; group loans linked to group; group repayment or reporting (if applicable) works on sample data.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'COL-01',
                            'feature' => 'Loan collateral & guarantors',
                            'detail' => 'Collateral/guarantor records attach to loan; documents stored; removal follows business rules.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'COL-02',
                            'feature' => 'Cash collateral',
                            'detail' => 'Deposit and withdrawal transactions post with correct balances; statements or prints match ledger.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '9. Accounting core',
                    'rows' => [
                        [
                            'id' => 'ACC-01',
                            'feature' => 'Chart of accounts',
                            'detail' => 'Accounts browsable; new accounts (if permitted) post correctly; invalid combinations prevented.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'ACC-02',
                            'feature' => 'Journals & entries',
                            'detail' => 'Journal creation, balanced lines, save draft, submit for approval, post, and reverse (if enabled) all behave correctly; GL balances update.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'ACC-03',
                            'feature' => 'Payment & receipt vouchers',
                            'detail' => 'Voucher lifecycle (create, approve, reject) works; posted vouchers appear in cash book / GL as expected.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'ACC-04',
                            'feature' => 'Bank accounts & reconciliation',
                            'detail' => 'Bank transactions import or capture; reconciliation clears matched items; unreconciled report accurate.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '10. Reports — loans & portfolio',
                    'rows' => [
                        [
                            'id' => 'RPT-01',
                            'feature' => 'Disbursement & repayment reports',
                            'detail' => 'Date and branch filters return expected rows; Excel/PDF export opens and matches on-screen totals for a test period.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'RPT-02',
                            'feature' => 'Portfolio, PAR, NPL, aging',
                            'detail' => 'Sample loans appear in correct PAR/NPL/aging buckets; totals tie to loan register for the same filter set.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'RPT-03',
                            'feature' => 'Loan arrears & provisioning / classification',
                            'detail' => 'Arrears and portfolio-provisioning reports respect arrears classifications; provision amounts match principal-in-arrears × rate for sampled loans.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'RPT-04',
                            'feature' => 'Expected vs collected',
                            'detail' => 'Expected collections from schedule compare to actual receipts for selected period; variances explainable.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '11. Reports — financial statements',
                    'rows' => [
                        [
                            'id' => 'FIN-01',
                            'feature' => 'Trial balance & GL',
                            'detail' => 'Trial balance balances; drill-down to GL lines matches journal postings for sampled accounts.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'FIN-02',
                            'feature' => 'Balance sheet & income statement',
                            'detail' => 'Statements generate for period; signs and classifications correct; comparative period (if used) consistent.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'FIN-03',
                            'feature' => 'Cash book & cash flow',
                            'detail' => 'Cash movements agree with bank/cash accounts; cash flow sections reconcile to opening/closing balances.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '12. Settings, communications & audit',
                    'rows' => [
                        [
                            'id' => 'SET-01',
                            'feature' => 'System & loan settings',
                            'detail' => 'Interest, fees, penalties, payment terms, file types, and SMS templates save and take effect on new transactions where applicable.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'SET-02',
                            'feature' => 'Arrears classifications',
                            'detail' => 'Buckets (DPD ranges, provision %) configurable; dashboard and reports use active set only.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'SET-03',
                            'feature' => 'Activity / audit logs',
                            'detail' => 'Sensitive actions logged with user, time, and change summary; log view filterable and exportable if offered.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '13. Customer API (mobile / partner)',
                    'rows' => [
                        [
                            'id' => 'API-01',
                            'feature' => 'Customer auth & profile',
                            'detail' => 'API login returns token or session per spec; profile endpoint returns same data as back-office for test customer.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'API-02',
                            'feature' => 'Loans & schedule visibility',
                            'detail' => 'Loans list and loan detail match back-office balances and schedule for the authenticated customer.',
                            'status' => null,
                            'comment' => null,
                        ],
                        [
                            'id' => 'API-03',
                            'feature' => 'Complaints & loan application via API',
                            'detail' => 'Submit complaint and submit loan application create records visible in back-office with correct customer linkage.',
                            'status' => null,
                            'comment' => null,
                        ],
                    ],
                ],
                [
                    'title' => '14. External integrations (API-dependent)',
                    'rows' => [
                        [
                            'id' => 'INT-UTU',
                            'feature' => 'Utumishi integration',
                            'detail' => 'Planned: automated verification of employment / salary data with Utumishi (Government payroll) for credit decisions and limits. UAT blocked until Utumishi API credentials, endpoints, and payload mapping are delivered and configured in a non-production environment.',
                            'status' => '✗',
                            'comment' => 'Wait API',
                        ],
                        [
                            'id' => 'INT-NIDA',
                            'feature' => 'NIDA integration',
                            'detail' => 'Planned: fetch or validate National ID (NIDA) biographic data and photo for KYC, deduplication, and regulatory alignment. UAT blocked until NIDA API access, consent workflow, and error-handling rules are confirmed and implemented.',
                            'status' => '✗',
                            'comment' => 'Wait API',
                        ],
                    ],
                ],
            ],
        ];
    }
}

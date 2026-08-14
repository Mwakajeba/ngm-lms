<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System features overview</title>
    <style>
        @page { margin: 14mm 16mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; line-height: 1.45; }
        .header { text-align: center; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #1a5f2a; }
        .logo { max-height: 44px; margin-bottom: 6px; }
        .title { font-size: 15px; font-weight: bold; color: #1a5f2a; margin: 6px 0 2px; }
        .subtitle { font-size: 9px; color: #555; }
        h2 { font-size: 11px; color: #1a5f2a; margin: 12px 0 5px; border-bottom: 1px solid #ccc; padding-bottom: 2px; }
        ul { margin: 4px 0 8px 16px; padding: 0; }
        li { margin-bottom: 3px; }
        .footer { margin-top: 16px; padding-top: 8px; border-top: 1px solid #ccc; font-size: 8px; color: #666; text-align: center; }
        .tagline { font-size: 9px; font-style: italic; color: #444; margin-top: 10px; }
    </style>
</head>
<body>
    @php
        $logoBase64 = null;
        $logoPath = null;
        if (isset($company) && $company && !empty($company->logo)) {
            $storagePath = public_path('storage/' . $company->logo);
            if (file_exists($storagePath)) { $logoPath = $storagePath; }
        }
        if (!$logoPath && file_exists(public_path('assets/images/icons/smartfinance.png'))) {
            $logoPath = public_path('assets/images/icons/smartfinance.png');
        }
        if (!$logoPath && file_exists(public_path('assets/images/logo-img.png'))) {
            $logoPath = public_path('assets/images/logo-img.png');
        }
        if ($logoPath && file_exists($logoPath)) {
            $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $logoType . ';base64,' . base64_encode($logoData);
        }
    @endphp

    <div class="header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo" class="logo">
        @endif
        <div class="title">Loan management system — feature overview</div>
        <div class="subtitle">
            {{ $company->name ?? config('app.name', 'Application') }}
            @if(isset($company) && $company && $company->phone) · {{ $company->phone }} @endif
            @if(isset($company) && $company && $company->email) · {{ $company->email }} @endif
        </div>
        <div class="subtitle">Generated: {{ $generatedAt->format('d M Y, H:i') }}</div>
    </div>

    <p><strong>Summary.</strong> Web-based loan origination and servicing with multi-branch operations, role-based access control, integrated accounting, and extensive portfolio and financial reporting.</p>

    <h2>Loan operations</h2>
    <ul>
        <li>Loan products, schedules, interest, fees, and repayment terms (configurable).</li>
        <li>Full loan lifecycle: application through approval, disbursement, servicing, completion, default, and write-off where enabled.</li>
        <li>Individual and group lending; loan officers and branch assignment.</li>
        <li>Collateral, guarantors, loan top-ups, and cash collateral (types and movements) where deployed.</li>
    </ul>

    <h2>Customers &amp; service</h2>
    <ul>
        <li>Customer profiles, documents, and history; registration workflows where used.</li>
        <li>Complaints and announcements; customer API for mobile or partner apps (profile, loans, repayments, applications, complaints).</li>
    </ul>

    <h2>Dashboard &amp; analytics</h2>
    <ul>
        <li>Executive dashboard with KPIs, charts, and activity (permission-based).</li>
        <li>Loan analytics; arrears and delinquency views; configurable arrears classifications and portfolio provisioning views.</li>
    </ul>

    <h2>Reporting — loans &amp; portfolio</h2>
    <ul>
        <li>Portfolio, performance, delinquency, disbursements, repayments, aging (balance and instalment), outstanding, arrears.</li>
        <li>Expected vs collected; portfolio at risk (PAR); internal portfolio analysis; non-performing loans (NPL).</li>
        <li>Portfolio provisioning &amp; arrears classification; exports to Excel and PDF on many reports.</li>
    </ul>

    <h2>Accounting &amp; treasury</h2>
    <ul>
        <li>Chart of accounts; journals with entries and approval / post / reverse flows.</li>
        <li>Payment and receipt vouchers; bank accounts and bank reconciliation.</li>
        <li>Suppliers, bill purchases, budgets; fees and penalties.</li>
        <li>General ledger, financial year, period close; financial statements and management reports (e.g. balance sheet, trial balance, income statement, cash book, cash flow).</li>
    </ul>

    <h2>Administration &amp; security</h2>
    <ul>
        <li>Multi-company and multi-branch; users assigned to branches.</li>
        <li>Granular roles and permissions; activity logging where enabled.</li>
        <li>System settings (rates, fees, penalties, terms, backups, messaging templates).</li>
        <li>Subscription awareness; secure authentication (including OTP / email flows and branch switching).</li>
    </ul>

    <h2>Regulatory &amp; management packs</h2>
    <ul>
        <li>Supervisory-style report modules (e.g. balance sheet, income statement, sectoral loans, interest rates, liquid assets, complaints, agent banking, geographical distribution) where enabled for your deployment.</li>
    </ul>

    <p class="tagline">Module availability depends on your subscription, permissions, and implementation choices. This document is indicative and not a binding specification.</p>

    <div class="footer">
        {{ config('app.name') }} · Feature overview for customer / partner use
    </div>
</body>
</html>

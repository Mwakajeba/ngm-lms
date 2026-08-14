<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UAT Feature Checklist — NGML</title>
    <style>
        @page { margin: 8mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; color: #111; line-height: 1.25; }
        .header { margin-bottom: 8px; padding-bottom: 10px; border-bottom: 2px solid #0d4f2d; width: 100%; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { border: none; vertical-align: top; padding: 0; }
        .logo { max-height: 36px; margin-bottom: 4px; }
        .co-name { font-size: 11px; font-weight: bold; color: #0d4f2d; }
        .co-meta { font-size: 7px; color: #333; margin-top: 2px; }
        /* Shrink-wrapped border so it ends after Total (4,200,000), not full column width */
        .pricing-wrap { margin-top: 5px; margin-bottom: 10px; }
        .pricing {
            display: inline-block;
            font-size: 7px;
            font-weight: bold;
            color: #0d4f2d;
            padding: 5px 8px;
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            white-space: nowrap;
        }
        .doc-block { margin-top: 0; padding-top: 2px; clear: both; }
        .doc-title { font-size: 10px; font-weight: bold; margin-top: 0; text-transform: uppercase; }
        .doc-meta { font-size: 7px; color: #333; margin-top: 4px; }
        .legend { font-size: 6px; color: #444; margin: 6px 0; padding: 4px; background: #f5f5f5; border: 1px solid #ccc; }
        table.main { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.main th, table.main td { border: 1px solid #333; padding: 3px 4px; vertical-align: top; }
        table.main th { background: #0d4f2d; color: #fff; font-size: 6.5px; text-align: center; }
        .col-num { width: 3%; text-align: center; }
        .col-id { width: 7%; }
        .col-feature { width: 14%; }
        .col-detail { width: 44%; font-size: 6.5px; }
        .col-pass { width: 5%; text-align: center; }
        .col-stat { width: 6%; text-align: center; }
        .col-com { width: 21%; min-height: 14px; }
        .section-row td { background: #e8f5e9; font-weight: bold; font-size: 7px; color: #0d4f2d; }
        .blocked { background: #ffebee; }
        .footer { margin-top: 8px; font-size: 6px; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 4px; }
    </style>
</head>
<body>
@php
    $c = $data['company'];
    $p = $data['pricing'] ?? ['system' => 0, 'integrations' => 0, 'total' => 0];
    $logoPath = public_path($c['logo_public']);
    $logoBase64 = null;
    if (file_exists($logoPath)) {
        $ext = pathinfo($logoPath, PATHINFO_EXTENSION);
        $logoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoPath));
    }
    $n = 0;
@endphp

<div class="header">
    <table class="header-table">
        <tr>
            <td style="width:72%;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo" class="logo"><br>
                @endif
                <div class="co-name">{{ $c['name'] }}</div>
                <div class="co-meta">Email: {{ $c['email'] }} &nbsp;|&nbsp; Phone: {{ $c['phone'] }}</div>
                <div class="pricing-wrap">
                    <div class="pricing">
                        Project — System: TZS {{ number_format($p['system']) }} &nbsp;|&nbsp;
                        Integrations: TZS {{ number_format($p['integrations']) }} &nbsp;|&nbsp;
                        <strong>Total: TZS {{ number_format($p['total']) }}</strong>
                    </div>
                </div>
                <div class="doc-block">
                    <div class="doc-title">User acceptance testing (UAT) — feature checklist</div>
                    <div class="doc-meta">Document version 1.0 &nbsp;|&nbsp; Generated: {{ $generatedAt->format('d M Y H:i') }}</div>
                </div>
            </td>
            <td style="width:28%;">
                <div class="co-meta">
                    <strong>Tester</strong><br>_________________<br><br>
                    <strong>Sign-off</strong><br>_________________<br><br>
                    <strong>Date</strong><br>_________________
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="legend">
    <strong>How to use:</strong> Tick <strong>Pass</strong> when the scenario is verified. Record defects, screen references, or dataset notes in <strong>Comments</strong>.
    <strong>Status ✗</strong> = not executable in this UAT cycle (blocked on external API). N/A = not deployed for this client.
</div>

<table class="main">
    <thead>
        <tr>
            <th class="col-num">#</th>
            <th class="col-id">ID</th>
            <th class="col-feature">Feature / area</th>
            <th class="col-detail">Detailed UAT — what to verify</th>
            <th class="col-pass">Pass<br>☐</th>
            <th class="col-stat">Status</th>
            <th class="col-com">Comments</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['sections'] as $section)
            <tr class="section-row">
                <td colspan="7">{{ $section['title'] }}</td>
            </tr>
            @foreach($section['rows'] as $row)
                @php $n++; @endphp
                <tr class="{{ ($row['status'] ?? null) === '✗' ? 'blocked' : '' }}">
                    <td class="col-num">{{ $n }}</td>
                    <td class="col-id">{{ $row['id'] }}</td>
                    <td class="col-feature">{{ $row['feature'] }}</td>
                    <td class="col-detail">{{ $row['detail'] }}</td>
                    <td class="col-pass">☐</td>
                    <td class="col-stat">{{ $row['status'] ?? '—' }}</td>
                    <td class="col-com">{{ $row['comment'] ?? '' }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<div class="footer">
    {{ $c['name'] }} — Confidential UAT worksheet. Integrations marked ✗ / Wait API are excluded from pass/fail until APIs are available.
</div>
</body>
</html>

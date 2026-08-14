<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>UAT Checklist — {{ $data['company']['name'] }}</title>
    <style>
        :root { --ngml: #0d4f2d; --muted: #5c5c5c; --line: #ccc; --blocked: #ffebee; }
        * { box-sizing: border-box; }
        body { font-family: system-ui, Segoe UI, Roboto, sans-serif; margin: 0; background: #f0f2f5; color: #1a1a1a; font-size: 13px; }
        .wrap { max-width: 1280px; margin: 0 auto; padding: 16px; }
        header { background: #fff; border-radius: 10px; padding: 20px 24px; margin-bottom: 16px; border: 1px solid var(--line); display: flex; flex-wrap: wrap; align-items: flex-start; gap: 20px; justify-content: space-between; }
        .brand { display: flex; gap: 16px; align-items: center; }
        .brand img { max-height: 56px; width: auto; }
        .brand h1 { margin: 0; font-size: 1.15rem; color: var(--ngml); }
        .brand p { margin: 4px 0 0; color: var(--muted); font-size: 0.9rem; }
        .pricing-wrap { margin-top: 10px; margin-bottom: 12px; }
        /* Border ends after Total (shrink-wrap), not full width — clear gap before title/date */
        .pricing-banner {
            display: inline-block;
            max-width: 100%;
            padding: 10px 12px;
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #0d4f2d;
            font-weight: 600;
            white-space: nowrap;
        }
        @media (max-width: 720px) {
            .pricing-banner { white-space: normal; }
        }
        .pricing-banner span { font-weight: 500; color: #333; }
        .doc-after-pricing { margin-top: 0; padding-top: 4px; clear: both; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .btn { display: inline-block; padding: 8px 14px; border-radius: 8px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; font-size: 0.875rem; }
        .btn-primary { background: var(--ngml); color: #fff; }
        .btn-outline { background: #fff; color: var(--ngml); border: 1px solid var(--ngml); }
        .legend { background: #e8f5e9; border: 1px solid #a5d6a7; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem; }
        .table-scroll { overflow-x: auto; background: #fff; border-radius: 10px; border: 1px solid var(--line); }
        table { width: 100%; border-collapse: collapse; min-width: 960px; }
        th, td { border: 1px solid var(--line); padding: 8px 10px; vertical-align: top; }
        th { background: var(--ngml); color: #fff; font-size: 0.75rem; text-align: left; position: sticky; top: 0; z-index: 2; }
        tr.section td { background: #e8f5e9; font-weight: 700; color: var(--ngml); font-size: 0.9rem; }
        tr.blocked { background: var(--blocked); }
        .col-n { width: 36px; text-align: center; }
        .col-id { width: 88px; font-family: ui-monospace, monospace; font-size: 0.8rem; }
        .col-pass { width: 56px; text-align: center; }
        .col-stat { width: 72px; text-align: center; font-weight: 600; }
        .detail { font-size: 0.82rem; color: #333; line-height: 1.45; }
        textarea.comment { width: 100%; min-height: 48px; resize: vertical; padding: 6px 8px; border: 1px solid #bbb; border-radius: 6px; font-family: inherit; font-size: 0.82rem; }
        input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
        .note-api { font-size: 0.8rem; color: #b71c1c; font-weight: 600; }
        footer { margin-top: 20px; text-align: center; font-size: 0.75rem; color: var(--muted); }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <div class="brand">
            <img src="{{ asset($data['company']['logo_public']) }}" alt="{{ $data['company']['name'] }}">
            <div>
                <h1>{{ $data['company']['name'] }}</h1>
                <p>{{ $data['company']['email'] }} · {{ $data['company']['phone'] }}</p>
                @php $pr = $data['pricing'] ?? ['system' => 0, 'integrations' => 0, 'total' => 0]; @endphp
                <div class="pricing-wrap">
                    <div class="pricing-banner">
                        <span>Project — System:</span> TZS {{ number_format($pr['system']) }}
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <span>Integrations:</span> TZS {{ number_format($pr['integrations']) }}
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <span>Total:</span> TZS {{ number_format($pr['total']) }}
                    </div>
                </div>
                <div class="doc-after-pricing">
                    <p style="margin:0 0 4px 0;"><strong>UAT feature checklist</strong> — tick Pass when verified; add comments for issues or evidence.</p>
                </div>
            </div>
        </div>
        <div class="actions">
            <button type="button" class="btn btn-outline" id="btnReset">Clear saved ticks</button>
            <button type="button" class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button>
            <a class="btn btn-primary" href="{{ route('docs.uat_checklist_pdf') }}">Download PDF (blank)</a>
        </div>
    </header>

    <div class="legend">
        <strong>Pass:</strong> tick when the detailed scenario is satisfied. <strong>Comments:</strong> log defect ID, steps, data used, or screenshots location.
        Rows with <strong>Status ✗</strong> and <span class="note-api">Wait API</span> are <em>out of scope</em> for UAT until Utumishi / NIDA APIs are delivered — leave Pass unticked.
        Your ticks and comments are saved in this browser only (local storage).
    </div>

    <div class="table-scroll">
        <table id="uatTable">
            <thead>
                <tr>
                    <th class="col-n">#</th>
                    <th class="col-id">ID</th>
                    <th>Feature / area</th>
                    <th style="min-width: 320px;">Detailed UAT — expected result</th>
                    <th class="col-pass">Pass</th>
                    <th class="col-stat">Status</th>
                    <th style="min-width: 220px;">Comments</th>
                </tr>
            </thead>
            <tbody>
                @php $num = 0; @endphp
                @foreach($data['sections'] as $section)
                    <tr class="section">
                        <td colspan="7">{{ $section['title'] }}</td>
                    </tr>
                    @foreach($section['rows'] as $row)
                        @php $num++; $rid = $row['id']; $blocked = ($row['status'] ?? null) === '✗'; @endphp
                        <tr class="{{ $blocked ? 'blocked' : '' }}" data-row="{{ $rid }}">
                            <td class="col-n">{{ $num }}</td>
                            <td class="col-id">{{ $rid }}</td>
                            <td><strong>{{ $row['feature'] }}</strong></td>
                            <td class="detail">{{ $row['detail'] }}</td>
                            <td class="col-pass">
                                @if(!$blocked)
                                    <input type="checkbox" class="uat-pass" data-id="{{ $rid }}" aria-label="Pass {{ $rid }}">
                                @else
                                    <span class="note-api">—</span>
                                @endif
                            </td>
                            <td class="col-stat">{{ $row['status'] ?? '—' }}</td>
                            <td>
                                @if($blocked)
                                    <span class="note-api">{{ $row['comment'] ?? 'Wait API' }}</span>
                                @endif
                                <textarea class="comment uat-comment" data-id="{{ $rid }}" placeholder="{{ $blocked ? 'Notes (blocked until API)…' : 'UAT comments…' }}" @if($blocked) style="margin-top:6px;" @endif></textarea>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <footer>
        NEXTGENERATION MICROFINANCE — UAT worksheet v1.0 · {{ $generatedAt->format('d M Y') }}
    </footer>
</div>
<script>
(function () {
    const KEY = 'ngml_uat_checklist_v1';
    function load() {
        try {
            const raw = localStorage.getItem(KEY);
            if (!raw) return;
            const o = JSON.parse(raw);
            document.querySelectorAll('.uat-pass').forEach(cb => {
                if (o.pass && o.pass[cb.dataset.id]) cb.checked = true;
            });
            document.querySelectorAll('textarea.uat-comment').forEach(ta => {
                if (o.comments && o.comments[ta.dataset.id] != null) ta.value = o.comments[ta.dataset.id];
            });
        } catch (e) {}
    }
    function save() {
        const pass = {};
        document.querySelectorAll('.uat-pass').forEach(cb => { pass[cb.dataset.id] = cb.checked; });
        const comments = {};
        document.querySelectorAll('textarea.uat-comment').forEach(ta => { comments[ta.dataset.id] = ta.value; });
        localStorage.setItem(KEY, JSON.stringify({ pass, comments, savedAt: new Date().toISOString() }));
    }
    load();
    document.querySelectorAll('.uat-pass').forEach(cb => cb.addEventListener('change', save));
    document.querySelectorAll('textarea.uat-comment').forEach(ta => {
        ta.addEventListener('input', function () { clearTimeout(ta._t); ta._t = setTimeout(save, 400); });
    });
    document.getElementById('btnReset').addEventListener('click', function () {
        if (!confirm('Clear all saved ticks and comments in this browser?')) return;
        localStorage.removeItem(KEY);
        document.querySelectorAll('.uat-pass').forEach(cb => { cb.checked = false; });
        document.querySelectorAll('textarea.uat-comment').forEach(ta => { ta.value = ''; });
    });
})();
</script>
</body>
</html>

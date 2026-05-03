@extends('layouts.main')

@section('title', 'Portfolio provisioning & arrears classification')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Portfolio provisioning & classification', 'url' => '#', 'icon' => 'bx bx-spreadsheet']
        ]" />

        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold text-dark mb-1">Portfolio provisioning &amp; arrears classification</h4>
                <p class="text-muted small mb-0">All active loans in scope. Each loan is placed in the first matching bucket by days in arrears. Provision amount = principal in arrears × bucket provision % (indicative, not posted to the GL).</p>
            </div>
            <a href="{{ route('accounting.loans.reports.portfolio_provisioning.export_excel', request()->query()) }}"
               class="btn btn-success btn-sm">
                <i class="bx bx-download me-1"></i>Export Excel
            </a>
        </div>

        @if($classifications->isEmpty())
            <div class="alert alert-warning border-0 shadow-sm">
                <strong>No active arrears classifications.</strong>
                Add or activate buckets under
                @can('manage system configurations')
                    <a href="{{ route('settings.arrears-classifications.index') }}">Settings → Loan arrears classifications</a>.
                @else
                    Settings → Loan arrears classifications (ask an administrator).
                @endcan
                This report needs at least one active bucket to show meaningful columns.
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bx bx-filter me-2"></i>Filter options</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('accounting.loans.reports.portfolio_provisioning') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="branch_id" class="form-label">Branch</label>
                            <select class="form-select" name="branch_id" id="branch_id">
                                @if(($branches->count() ?? 0) > 1)
                                    <option value="all" {{ ($branchId === 'all' || $branchId === null || $branchId === '') ? 'selected' : '' }}>All my branches</option>
                                @endif
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="group_id" class="form-label">Group</label>
                            <select class="form-select" name="group_id" id="group_id">
                                <option value="">All groups</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ (string) $groupId === (string) $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="loan_officer_id" class="form-label">Loan officer</label>
                            <select class="form-select" name="loan_officer_id" id="loan_officer_id">
                                <option value="">All officers</option>
                                @foreach($loanOfficers as $officer)
                                    <option value="{{ $officer->id }}" {{ (string) $loanOfficerId === (string) $officer->id ? 'selected' : '' }}>
                                        {{ $officer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search me-1"></i>Apply
                            </button>
                            <a href="{{ route('accounting.loans.reports.portfolio_provisioning') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 70vh;">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0" style="min-width: 1100px;">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Customer</th>
                                <th>Customer no.</th>
                                <th>Phone</th>
                                <th>Loan no.</th>
                                <th>Branch</th>
                                <th>Loan officer</th>
                                <th class="text-end">Principal disbursed</th>
                                <th class="text-end">Outstanding principal</th>
                                <th class="text-end">Days in arrears</th>
                                <th class="text-end">Past due days</th>
                                <th class="text-end">Principal in arrears</th>
                                <th class="text-end">Interest in arrears</th>
                                <th class="text-end">Total in arrears</th>
                                @foreach($classifications as $c)
                                    <th class="text-end small" title="{{ $c->days_from }}–{{ $c->days_to ?? '∞' }} DPD">
                                        {{ $c->bucket_label }} — {{ $c->status }}<br>
                                        <span class="text-muted">({{ number_format((float) $c->provision_percentage, 2) }}%)</span>
                                    </th>
                                @endforeach
                                <th class="text-end">Provision rate %</th>
                                <th class="text-end">Provision amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $r)
                                <tr>
                                    <td>{{ $r['customer_name'] }}</td>
                                    <td>{{ $r['customer_no'] }}</td>
                                    <td>{{ $r['phone'] }}</td>
                                    <td>{{ $r['loan_no'] }}</td>
                                    <td>{{ $r['branch'] }}</td>
                                    <td>{{ $r['loan_officer'] }}</td>
                                    <td class="text-end">{{ number_format($r['principal_disbursed'], 2) }}</td>
                                    <td class="text-end">{{ number_format($r['outstanding_principal'], 2) }}</td>
                                    <td class="text-end">{{ $r['days_in_arrears'] }}</td>
                                    <td class="text-end">{{ $r['past_due_days'] }}</td>
                                    <td class="text-end">{{ number_format($r['principal_in_arrears'], 2) }}</td>
                                    <td class="text-end">{{ number_format($r['interest_in_arrears'], 2) }}</td>
                                    <td class="text-end">{{ number_format($r['total_in_arrears'], 2) }}</td>
                                    @foreach($classifications as $c)
                                        <td class="text-end">{{ number_format($r['buckets'][$c->id] ?? 0, 2) }}</td>
                                    @endforeach
                                    <td class="text-end">{{ number_format($r['provision_rate'], 2) }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($r['provision_amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 15 + $classifications->count() }}" class="text-center text-muted py-4">No active loans match the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($rows))
                            <tfoot class="table-secondary fw-semibold">
                                <tr>
                                    <td colspan="6">Totals</td>
                                    <td class="text-end">{{ number_format($totals['principal_disbursed'], 2) }}</td>
                                    <td class="text-end">{{ number_format($totals['outstanding_principal'], 2) }}</td>
                                    <td class="text-end">—</td>
                                    <td class="text-end">—</td>
                                    <td class="text-end">{{ number_format($totals['principal_in_arrears'], 2) }}</td>
                                    <td class="text-end">{{ number_format($totals['interest_in_arrears'], 2) }}</td>
                                    <td class="text-end">{{ number_format($totals['total_in_arrears'], 2) }}</td>
                                    @foreach($classifications as $c)
                                        <td class="text-end">{{ number_format($totals['buckets'][$c->id] ?? 0, 2) }}</td>
                                    @endforeach
                                    <td class="text-end">—</td>
                                    <td class="text-end">{{ number_format($totals['provision_amount'], 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

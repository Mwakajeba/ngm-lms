@extends('layouts.main')

@section('title', 'Double Entry - ' . $category)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting', 'url' => '#', 'icon' => 'bx bx-calculator'],
            ['label' => 'Transactions', 'url' => '#', 'icon' => 'bx bx-transfer'],
            ['label' => 'Double Entry - ' . $category, 'url' => '#', 'icon' => 'bx bx-list-ul']
        ]" />

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-book-open me-1 font-22 text-primary"></i></div>
                                    <h5 class="mb-0 text-primary">Double Entry</h5>
                                </div>
                                <p class="mb-0 text-muted">Transactions for {{ $category }}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row row-cols-1 row-cols-lg-2">
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Debit</p>
                                <h4 class="font-weight-bold text-success">{{ number_format($totalDebit, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-success text-white">
                                <i class='bx bx-trending-up'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Credit</p>
                                <h4 class="font-weight-bold text-danger">{{ number_format($totalCredit, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-danger text-white">
                                <i class='bx bx-trending-down'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Transaction History</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Account</th>
                                        <th>Description</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->date ? $transaction->date->format('d-m-Y') : 'N/A' }}</td>
                                        <td>
                                            @if($transaction->chartAccount)
                                                <span class="fw-medium">{{ $transaction->chartAccount->account_name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($transaction->description, 50) }}</td>
                                        <td class="text-end">
                                            @if($transaction->nature == 'debit')
                                                <span class="text-success fw-bold">{{ number_format($transaction->amount, 2) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($transaction->nature == 'credit')
                                                <span class="text-danger fw-bold">{{ number_format($transaction->amount, 2) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle me-2"></i>No transactions found for this category
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
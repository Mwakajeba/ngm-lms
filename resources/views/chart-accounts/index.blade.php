@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Chart of Accounts')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <!-- Breadcrumbs -->
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Chart of Accounts', 'url' => '#', 'icon' => 'bx bx-spreadsheet']
             ]" />
            <!-- End Breadcrumbs -->

            <div class="row row-cols-1 row-cols-lg-3">
                <div class="col">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0">Total</p>
                                    <h4 class="font-weight-bold">{{ $chartAccounts->total() }}</h4>
                                </div>
                                <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-refresh'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->

            <h6 class="mb-0 text-uppercase">CHART OF ACCOUNTS</h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Chart of Accounts</h5>
                        <a href="{{ route('accounting.chart-accounts.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> Add New Account
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account Class</th>
                                    <th>Account Group</th>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th>Cash Flow</th>
                                    @php
                                        $hasCashFlowCategories = $chartAccounts->contains(function ($account) {
                                            return $account->has_cash_flow && $account->cashFlowCategory;
                                        });
                                    @endphp
                                    @if($hasCashFlowCategories)
                                        <th>Cash Flow Category</th>
                                    @endif
                                    <th>Equity</th>
                                    @php
                                        $hasEquityCategories = $chartAccounts->contains(function ($account) {
                                            return $account->has_equity && $account->equityCategory;
                                        });
                                    @endphp
                                    @if($hasEquityCategories)
                                        <th>Equity Category</th>
                                    @endif
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chartAccounts as $index => $account)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $account->accountClassGroup->accountClass->name ?? 'N/A' }}</td>
                                        <td>{{ $account->accountClassGroup->name ?? 'N/A' }}</td>
                                        <td>{{ $account->account_code }}</td>
                                        <td>{{ $account->account_name }}</td>
                                        <td>
                                            @if($account->has_cash_flow)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        @if($hasCashFlowCategories)
                                            <td>
                                                @if($account->has_cash_flow && $account->cashFlowCategory)
                                                    <span class="badge bg-info"
                                                        title="{{ $account->cashFlowCategory->description ?? '' }}">
                                                        <i class="bx bx-money-withdraw me-1"></i>{{ $account->cashFlowCategory->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                            @if($account->has_equity)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        @if($hasEquityCategories)
                                            <td>
                                                @if($account->has_equity && $account->equityCategory)
                                                    <span class="badge bg-warning"
                                                        title="{{ $account->equityCategory->description ?? '' }}">
                                                        <i class="bx bx-pie-chart-alt me-1"></i>{{ $account->equityCategory->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td>{{ $account->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('accounting.chart-accounts.show', Hashids::encode($account->id)) }}"
                                                class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="{{ route('accounting.chart-accounts.edit', Hashids::encode($account->id)) }}"
                                                class="btn btn-sm btn-outline-warning">Edit</a>

                                            <form
                                                action="{{ route('accounting.chart-accounts.destroy', Hashids::encode($account->id)) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-name="{{ $account->account_name }}">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $chartAccounts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    <footer class="page-footer">
        <p class="mb-0">Copyright © 2021. All right reserved.</p>
    </footer>
@endsection
@extends('layouts.app')
@section('page-title')
    {{ __('Loan Applications') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Loan Applications') }}</a>
        </li>
    </ul>
@endsection
@section('content')
    <div class="row">
        <!-- Available Loan Types -->
        <div class="col-xl-8 col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Available Loan Types') }}</h4>
                    <small class="text-muted">{{ __('Choose a loan type to apply for') }}</small>
                </div>
                <div class="card-body">
                    @if($loanTypes->count() > 0)
                        <div class="row">
                            @foreach($loanTypes as $loanType)
                                <div class="col-md-6 mb-4">
                                    <div class="card loan-type-card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title text-primary">{{ $loanType->type }}</h5>
                                            <div class="loan-details">
                                                <p class="mb-2">
                                                    <strong>{{ __('Loan Amount:') }}</strong><br>
                                                    {{ priceFormat($loanType->min_loan_amount) }} - {{ priceFormat($loanType->max_loan_amount) }}
                                                </p>
                                                <p class="mb-2">
                                                    <strong>{{ __('Interest Rate:') }}</strong><br>
                                                    {{ $loanType->interest_rate }}% {{ __('per year') }}
                                                </p>
                                                <p class="mb-2">
                                                    <strong>{{ __('Interest Type:') }}</strong><br>
                                                    {{ \App\Models\LoanType::$interestType[$loanType->interest_type] }}
                                                </p>
                                                <p class="mb-2">
                                                    <strong>{{ __('Max Term:') }}</strong><br>
                                                    {{ $loanType->max_loan_term }} {{ $loanType->loan_term_period }}
                                                </p>
                                                <p class="mb-2">
                                                    <strong>{{ __('Penalty:') }}</strong><br>
                                                    {{ $loanType->penalties }}%
                                                </p>
                                                @if($loanType->notes)
                                                    <p class="mb-2">
                                                        <strong>{{ __('Notes:') }}</strong><br>
                                                        <small class="text-muted">{{ $loanType->notes }}</small>
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="mt-3">
                                                <a href="{{ route('loan.apply', encrypt($loanType->id)) }}" class="btn btn-primary btn-sm w-100">
                                                    <i data-feather="file-plus"></i> {{ __('Apply for this Loan') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i data-feather="info" class="text-muted" style="width: 48px; height: 48px;"></i>
                            <h5 class="mt-3 text-muted">{{ __('No Loan Types Available') }}</h5>
                            <p class="text-muted">{{ __('Please contact the administrator for available loan options.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- My Loan Applications -->
        <div class="col-xl-4 col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('My Loan Applications') }}</h4>
                </div>
                <div class="card-body">
                    @if($myLoans->count() > 0)
                        @foreach($myLoans as $loan)
                            <div class="loan-item mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ loanPrefix() . $loan->loan_id }}</h6>
                                        <p class="mb-1 text-muted small">{{ !empty($loan->loanType) ? $loan->loanType->type : '' }}</p>
                                        <p class="mb-1"><strong>{{ priceFormat($loan->amount) }}</strong></p>
                                    </div>
                                    <div class="text-end">
                                        @if($loan->status == 'pending')
                                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                                        @elseif($loan->status == 'approved')
                                            <span class="badge bg-success">{{ __('Approved') }}</span>
                                        @elseif($loan->status == 'rejected')
                                            <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($loan->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">{{ __('Applied on:') }} {{ $loan->created_at->format('M d, Y') }}</small>
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('loan.show', encrypt($loan->id)) }}" class="btn btn-outline-primary btn-sm">
                                        <i data-feather="eye"></i> {{ __('View Details') }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i data-feather="file-text" class="text-muted" style="width: 32px; height: 32px;"></i>
                            <p class="mt-2 text-muted">{{ __('No loan applications yet') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .loan-type-card {
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }
        .loan-type-card:hover {
            border-color: #4e73df;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transform: translateY(-2px);
        }
        .loan-details p {
            font-size: 0.9rem;
        }
        .loan-item {
            background-color: #f8f9fc;
        }
    </style>
@endsection
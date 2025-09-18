@extends('layouts.app')

@section('page-title')
    {{ __('Loan Disbursements') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Loan Disbursements') }}</li>
@endsection

@section('action-button')
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Approved Loans Pending Disbursement') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Loan Code') }}</th>
                                    <th>{{ __('Borrower') }}</th>
                                    <th>{{ __('Loan Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('File Charges') }}</th>
                                    <th>{{ __('File Charges Status') }}</th>
                                    <th>{{ __('Disbursement Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                    <tr>
                                        <td>{{ $loan->loan_code }}</td>
                                        <td>{{ $loan->Customers->name ?? 'N/A' }}</td>
                                        <td>{{ $loan->loanType->name ?? 'N/A' }}</td>
                                        <td>{{ currency_format_with_sym($loan->amount) }}</td>
                                        <td>
                                            @if($loan->loanType->file_charges && $loan->loanType->file_charges > 0)
                                                {{ currency_format_with_sym($loan->file_charges_amount) }}
                                            @else
                                                <span class="text-muted">{{ __('No charges') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($loan->loanType->file_charges && $loan->loanType->file_charges > 0)
                                                <span class="badge bg-{{ $loan->file_charges_status === 'paid' ? 'success' : ($loan->file_charges_status === 'waived' ? 'info' : 'warning') }}">
                                                    {{ ucfirst($loan->file_charges_status) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('N/A') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $loan->disbursement_status === 'disbursed' ? 'success' : 'warning' }}">
                                                {{ ucfirst($loan->disbursement_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('disbursement.show', $loan->id) }}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-eye"></i> {{ __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">{{ __('No approved loans pending disbursement') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- File Charges History -->
    <div class="row mt-4">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Recent File Charges & Disbursement Transactions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Loan Code') }}</th>
                                    <th>{{ __('Transaction Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Payment Method') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Processed By') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                                        <td>{{ $transaction->loan->loan_code }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->transaction_type === 'file_charges' ? 'warning' : 'success' }}">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                            </span>
                                        </td>
                                        <td>₹{{ number_format($transaction->amount) }}</td>
                                        <td>{{ ucfirst($transaction->payment_method) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'verified' ? 'info' : 'warning') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $transaction->processedBy->name ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('No recent transactions') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('layouts.app')

@section('page-title')
    {{ __('Loan Disbursement Details') }} - {{ $loan->loan_code }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('disbursement.index') }}">{{ __('Disbursements') }}</a></li>
    <li class="breadcrumb-item">{{ $loan->loan_code }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Loan Details -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Loan Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>{{ __('Loan Code:') }}</strong>
                            <p>{{ $loan->loan_code }}</p>
                        </div>
                        <div class="col-sm-6">
                            <strong>{{ __('Borrower:') }}</strong>
                            <p>{{ $loan->Customers->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <strong>{{ __('Loan Type:') }}</strong>
                            <p>{{ $loan->loanType->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <strong>{{ __('Loan Amount:') }}</strong>
                            <p>{{ currency_format_with_sym($loan->amount) }}</p>
                        </div>
                        <div class="col-sm-6">
                            <strong>{{ __('Status:') }}</strong>
                            <p><span class="badge bg-success">{{ ucfirst($loan->status) }}</span></p>
                        </div>
                        <div class="col-sm-6">
                            <strong>{{ __('Created Date:') }}</strong>
                            <p>{{ $loan->created_at ? $loan->created_at->format('d M Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- File Charges Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('File Charges') }}</h5>
                </div>
                <div class="card-body">
                    @if($loan->loanType->file_charges && $loan->loanType->file_charges > 0)
                        <div class="row">
                            <div class="col-sm-6">
                                <strong>{{ __('File Charges:') }}</strong>
                                <p>{{ currency_format_with_sym($loan->file_charges_amount) }}</p>
                            </div>
                            <div class="col-sm-6">
                                <strong>{{ __('Status:') }}</strong>
                                <p>
                                    <span class="badge bg-{{ $loan->file_charges_status === 'paid' ? 'success' : ($loan->file_charges_status === 'waived' ? 'info' : 'warning') }}">
                                        {{ ucfirst($loan->file_charges_status) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        @if($loan->file_charges_status === 'pending')
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>{{ __('Process File Charges') }}</h6>
                                    
                                    <!-- File Charges Payment Form -->
                                    <form action="{{ route('disbursement.pay-file-charges', $loan->id) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="payment_method">{{ __('Payment Method') }}</label>
                                                    <select name="payment_method" id="payment_method" class="form-control" required>
                                                        <option value="">{{ __('Select Payment Method') }}</option>
                                                        <option value="cash">{{ __('Cash') }}</option>
                                                        <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                                        <option value="cheque">{{ __('Cheque') }}</option>
                                                        <option value="online">{{ __('Online Payment') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="transaction_reference">{{ __('Transaction Reference') }}</label>
                                                    <input type="text" name="transaction_reference" id="transaction_reference" class="form-control" placeholder="{{ __('Enter reference number') }}">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="payment_proof">{{ __('Payment Proof (Optional)') }}</label>
                                                    <input type="file" name="payment_proof" id="payment_proof" class="form-control" accept="image/*,application/pdf">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="notes">{{ __('Notes') }}</label>
                                                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="{{ __('Add any notes...') }}"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="ti ti-check"></i> {{ __('Mark as Paid') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Waive File Charges Form -->
                                    <form action="{{ route('disbursement.waive-file-charges', $loan->id) }}" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="waive_reason">{{ __('Waiver Reason') }}</label>
                                                    <textarea name="waive_reason" id="waive_reason" class="form-control" rows="2" placeholder="{{ __('Reason for waiving file charges...') }}"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-info">
                                                    <i class="ti ti-ban"></i> {{ __('Waive File Charges') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">{{ __('No file charges applicable for this loan type.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Disbursement Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Loan Disbursement') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ __('Disbursement Status:') }}</strong>
                            <p>
                                <span class="badge bg-{{ $loan->disbursement_status === 'disbursed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($loan->disbursement_status) }}
                                </span>
                            </p>
                        </div>
                        @if($loan->disbursed_at)
                            <div class="col-md-6">
                                <strong>{{ __('Disbursed Date:') }}</strong>
                                <p>{{ \Carbon\Carbon::parse($loan->disbursed_at)->format('d M Y, H:i') }}</p>
                            </div>
                        @endif
                    </div>

                    @if($loan->disbursement_notes)
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>{{ __('Disbursement Notes:') }}</strong>
                                <p>{{ $loan->disbursement_notes }}</p>
                            </div>
                        </div>
                    @endif

                    @php
                        $canDisburse = $loan->status === 'approved' && 
                                      $loan->disbursement_status === 'pending' && 
                                      (!$loan->loanType->file_charges || $loan->loanType->file_charges <= 0 || in_array($loan->file_charges_status, ['paid', 'waived']));
                    @endphp

                    @if($canDisburse)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>{{ __('Process Loan Disbursement') }}</h6>
                                <form action="{{ route('disbursement.disburse-loan', $loan->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="disbursement_method">{{ __('Disbursement Method') }}</label>
                                                <select name="disbursement_method" id="disbursement_method" class="form-control" required>
                                                    <option value="">{{ __('Select Disbursement Method') }}</option>
                                                    <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                                    <option value="cheque">{{ __('Cheque') }}</option>
                                                    <option value="cash">{{ __('Cash') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="disbursement_reference">{{ __('Transaction Reference') }}</label>
                                                <input type="text" name="disbursement_reference" id="disbursement_reference" class="form-control" placeholder="{{ __('Enter reference number') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="disbursement_proof">{{ __('Disbursement Proof (Optional)') }}</label>
                                                <input type="file" name="disbursement_proof" id="disbursement_proof" class="form-control" accept="image/*,application/pdf">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="disbursement_notes">{{ __('Disbursement Notes') }}</label>
                                                <textarea name="disbursement_notes" id="disbursement_notes" class="form-control" rows="3" placeholder="{{ __('Add disbursement notes...') }}"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti ti-send"></i> {{ __('Disburse Loan Amount') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($loan->disbursement_status === 'pending')
                        <div class="alert alert-warning">
                            @if($loan->loanType->file_charges && $loan->loanType->file_charges > 0 && $loan->file_charges_status === 'pending')
                                {{ __('File charges must be paid or waived before loan can be disbursed.') }}
                            @else
                                {{ __('Loan is not ready for disbursement.') }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    @if($loan->disbursements->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Transaction History') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Transaction Type') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Payment Method') }}</th>
                                        <th>{{ __('Reference') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Processed By') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->disbursements as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $transaction->transaction_type === 'file_charges' ? 'warning' : 'success' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                                </span>
                                            </td>
                                            <td>{{ currency_format_with_sym($transaction->amount) }}</td>
                                            <td>{{ ucfirst($transaction->payment_method) }}</td>
                                            <td>{{ $transaction->transaction_reference ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'verified' ? 'info' : 'warning') }}">
                                                    {{ ucfirst($transaction->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $transaction->processedBy->name ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
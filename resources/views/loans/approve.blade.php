@extends('layouts.app')
@section('page-title')
    {{ __('Loan Approval') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('loan.index') }}">{{ __('Loans') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Approve') }}</a>
        </li>
    </ul>
@endsection
@section('content')
    <div class="row">
        {{ Form::model($loan, ['route' => ['loan.updateApproval', encrypt($loan->id)], 'method' => 'PUT']) }}
        
        <!-- Loan Application Details -->
        <div class="col-xl-4 col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Application Details') }}</h4>
                </div>
                <div class="card-body">
                    <div class="application-info">
                        <div class="info-item mb-3">
                            <strong>{{ __('Loan ID:') }}</strong><br>
                            <span class="text-primary">{{ loanPrefix() . $loan->loan_id }}</span>
                        </div>
                        
                        <div class="info-item mb-3">
                            <strong>{{ __('Customer:') }}</strong><br>
                            {{ $loan->Customers ? $loan->Customers->name : 'N/A' }}<br>
                            <small class="text-muted">{{ $loan->Customers ? $loan->Customers->email : '' }}</small>
                        </div>
                        
                        <div class="info-item mb-3">
                            <strong>{{ __('Loan Type:') }}</strong><br>
                            {{ $loan->loanType ? $loan->loanType->type : 'N/A' }}
                        </div>
                        
                        <div class="info-item mb-3">
                            <strong>{{ __('Requested Amount:') }}</strong><br>
                            <span class="text-success h5">{{ priceFormat($loan->amount) }}</span>
                        </div>
                        
                        <div class="info-item mb-3">
                            <strong>{{ __('Purpose:') }}</strong><br>
                            <small>{{ $loan->purpose_of_loan }}</small>
                        </div>
                        
                        <div class="info-item mb-3">
                            <strong>{{ __('Current Status:') }}</strong><br>
                            @if($loan->status == 'pending')
                                <span class="badge bg-warning">{{ __('Pending Review') }}</span>
                            @elseif($loan->status == 'approved')
                                <span class="badge bg-success">{{ __('Approved') }}</span>
                            @elseif($loan->status == 'rejected')
                                <span class="badge bg-danger">{{ __('Rejected') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($loan->status) }}</span>
                            @endif
                        </div>
                        
                        <div class="info-item mb-3">
                            <strong>{{ __('Applied On:') }}</strong><br>
                            <small class="text-muted">{{ $loan->created_at->format('M d, Y h:i A') }}</small>
                        </div>
                        
                        @if($loan->notes)
                            <div class="info-item mb-3">
                                <strong>{{ __('Customer Notes:') }}</strong><br>
                                <small class="text-muted">{{ $loan->notes }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Loan Type Constraints -->
            @if($loan->loanType)
            <div class="card mt-3">
                <div class="card-header">
                    <h4>{{ __('Loan Type Limits') }}</h4>
                </div>
                <div class="card-body">
                    <div class="constraint-info">
                        <div class="info-item mb-2">
                            <strong>{{ __('Amount Range:') }}</strong><br>
                            {{ priceFormat($loan->loanType->min_loan_amount) }} - {{ priceFormat($loan->loanType->max_loan_amount) }}
                        </div>
                        
                        <div class="info-item mb-2">
                            <strong>{{ __('Interest Rate:') }}</strong><br>
                            {{ $loan->loanType->interest_rate }}% {{ __('per year') }}
                        </div>
                        
                        <div class="info-item mb-2">
                            <strong>{{ __('Max Term:') }}</strong><br>
                            {{ $loan->loanType->max_loan_term }} {{ $loan->loanType->loan_term_period }}
                        </div>
                        
                        <div class="info-item mb-2">
                            <strong>{{ __('Penalty:') }}</strong><br>
                            {{ $loan->loanType->penalties }}%
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Approval Form -->
        <div class="col-xl-8 col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Loan Approval & Modification') }}</h4>
                    <small class="text-muted">{{ __('Review and modify loan parameters before approval') }}</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Approval Status -->
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('status', __('Approval Decision'), ['class' => 'form-label']) }}
                            {!! Form::select('status', $status, null, [
                                'class' => 'form-control',
                                'required' => 'required',
                                'id' => 'approval_status'
                            ]) !!}
                        </div>
                        
                        <!-- Branch -->
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                            {!! Form::select('branch_id', $branch, null, [
                                'class' => 'form-control',
                                'required' => 'required',
                            ]) !!}
                        </div>
                        
                        <!-- Approved Amount -->
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('amount', __('Approved Amount'), ['class' => 'form-label']) }}
                            {{ Form::number('amount', null, [
                                'class' => 'form-control', 
                                'step' => 0.01, 
                                'placeholder' => __('Enter approved amount'), 
                                'required' => 'required',
                                'id' => 'approved_amount'
                            ]) }}
                            <small class="text-muted">{{ __('You can modify the requested amount') }}</small>
                        </div>
                        
                        <!-- Loan Terms -->
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('loan_terms', __('Approved Term'), ['class' => 'form-label']) }}
                            {{ Form::number('loan_terms', null, [
                                'class' => 'form-control', 
                                'step' => 1, 
                                'placeholder' => __('Enter approved term'), 
                                'required' => 'required'
                            ]) }}
                        </div>
                        
                        <!-- Term Period -->
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('loan_term_period', __('Term Period'), ['class' => 'form-label']) }}
                            {!! Form::select('loan_term_period', $termPeroid, null, [
                                'class' => 'form-control',
                                'required' => 'required',
                            ]) !!}
                        </div>
                        
                        <!-- Auto-calculated dates info -->
                        <div class="form-group col-md-12 col-lg-12">
                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="fas fa-info-circle"></i> {{ __('Automatic Date Calculation') }}</h6>
                                <p class="mb-1"><strong>{{ __('Start Date:') }}</strong> {{ __('Will be set to today when loan is approved') }}</p>
                                <p class="mb-0"><strong>{{ __('Due Date:') }}</strong> {{ __('Will be calculated automatically based on loan terms and payment schedule') }}</p>
                            </div>
                        </div>
                        
                        <!-- Admin Notes -->
                        <div class="form-group col-md-12 col-lg-12">
                            {{ Form::label('admin_notes', __('Admin Notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('admin_notes', null, [
                                'class' => 'form-control', 
                                'rows' => '3', 
                                'placeholder' => __('Add notes about the approval decision (optional)')
                            ]) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="card mt-3">
                <div class="card-body text-center">
                    <div class="approval-actions">
                        <button type="submit" class="btn btn-success btn-lg mr-2" id="approve_btn">
                            <i data-feather="check-circle"></i> {{ __('Approve Loan') }}
                        </button>
                        
                        <button type="submit" class="btn btn-danger btn-lg mr-2" id="reject_btn">
                            <i data-feather="x-circle"></i> {{ __('Reject Loan') }}
                        </button>
                        
                        <button type="submit" class="btn btn-warning btn-lg mr-2" id="review_btn">
                            <i data-feather="clock"></i> {{ __('Mark Under Review') }}
                        </button>
                        
                        <a href="{{ route('loan.index') }}" class="btn btn-secondary btn-lg">
                            <i data-feather="arrow-left"></i> {{ __('Back to Loans') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        {{ Form::close() }}
    </div>
    
    <style>
        .application-info, .constraint-info {
            background-color: #f8f9fc;
            padding: 20px;
            border-radius: 8px;
        }
        .info-item {
            padding: 8px 0;
            border-bottom: 1px solid #e3e6f0;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .approval-actions .btn {
            margin: 5px;
        }
        #approved_amount {
            font-size: 1.1em;
            font-weight: bold;
        }
    </style>
    
    <script>
        $(document).ready(function() {
            // Handle approval action buttons
            $('#approve_btn').click(function(e) {
                e.preventDefault();
                $('#approval_status').val('approved');
                $(this).closest('form').submit();
            });
            
            $('#reject_btn').click(function(e) {
                e.preventDefault();
                if (confirm('{{ __('Are you sure you want to reject this loan application?') }}')) {
                    $('#approval_status').val('rejected');
                    $(this).closest('form').submit();
                }
            });
            
            $('#review_btn').click(function(e) {
                e.preventDefault();
                $('#approval_status').val('under_review');
                $(this).closest('form').submit();
            });
            
            // Status change handler
            $('#approval_status').change(function() {
                var status = $(this).val();
                if (status === 'approved') {
                    $('#approved_amount').focus();
                }
            });
        });
    </script>
@endsection
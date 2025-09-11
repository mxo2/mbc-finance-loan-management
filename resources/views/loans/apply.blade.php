@extends('layouts.app')
@section('page-title')
    {{ __('Apply for Loan') }}
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
            <a href="#">{{ __('Apply') }}</a>
        </li>
    </ul>
@endsection
@section('content')
    <div class="row">
        {{ Form::open(['route' => 'loan.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
        
        <!-- Loan Type Information -->
        <div class="col-xl-4 col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Loan Type Details') }}</h4>
                </div>
                <div class="card-body">
                    <div class="loan-type-info">
                        <h5 class="text-primary mb-3">{{ $loanType->type }}</h5>
                        
                        <div class="info-item mb-3">
                            <strong>{{ __('Loan Amount Range:') }}</strong><br>
                            <span class="text-success">{{ priceFormat($loanType->min_loan_amount) }} - {{ priceFormat($loanType->max_loan_amount) }}</span>
                        </div>
                        
                        <div class="info-item mb-3">
                            <strong>{{ __('Interest Rate:') }}</strong><br>
                            <span class="text-info">{{ $loanType->interest_rate }}% {{ __('per year') }}</span>
                        </div>
                        
                        <div class="info-item mb-3">
                            <strong>{{ __('Interest Type:') }}</strong><br>
                            {{ \App\Models\LoanType::$interestType[$loanType->interest_type] }}
                        </div>
                        
                        <div class="info-item mb-3">
                            <strong>{{ __('Maximum Term:') }}</strong><br>
                            {{ $loanType->max_loan_term }} {{ $loanType->loan_term_period }}
                        </div>
                        
                        <div class="info-item mb-3">
                            <strong>{{ __('Late Payment Penalty:') }}</strong><br>
                            <span class="text-warning">{{ $loanType->penalties }}%</span>
                        </div>
                        
                        @if($loanType->notes)
                            <div class="info-item mb-3">
                                <strong>{{ __('Additional Information:') }}</strong><br>
                                <small class="text-muted">{{ $loanType->notes }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Application Form -->
        <div class="col-xl-8 col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Loan Application Form') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Hidden fields -->
                        {{ Form::hidden('loan_id', $loanNumber) }}
                        {{ Form::hidden('loan_type', $loanType->id) }}
                        {{ Form::hidden('customer', \Auth::user()->id) }}
                        {{ Form::hidden('status', 'pending') }}
                        {{ Form::hidden('created_by', \Auth::user()->id) }}
                        
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                            {!! Form::select('branch_id', $branch, null, [
                                'class' => 'form-control',
                                'required' => 'required',
                            ]) !!}
                        </div>
                        
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('amount', __('Requested Amount'), ['class' => 'form-label']) }}
                            {{ Form::number('amount', null, [
                                'class' => 'form-control', 
                                'step' => 0.01, 
                                'placeholder' => __('Enter requested amount'), 
                                'required' => 'required',
                                'min' => $loanType->min_loan_amount,
                                'max' => $loanType->max_loan_amount
                            ]) }}
                            <small class="text-muted">{{ __('Amount must be between') }} {{ priceFormat($loanType->min_loan_amount) }} {{ __('and') }} {{ priceFormat($loanType->max_loan_amount) }}</small>
                        </div>
                        
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('referral_code', __('Referral Code'), ['class' => 'form-label']) }}
                            {{ Form::text('referral_code', null, [
                                'class' => 'form-control', 
                                'placeholder' => __('Enter referral code (required)'), 
                                'required' => 'required'
                            ]) }}
                            <small class="text-muted">{{ __('Please enter a valid referral code') }}</small>
                        </div>
                        
                        <!-- Hidden fields for loan terms - will be set from loan type -->
                        {{ Form::hidden('loan_terms', $loanType->max_loan_term) }}
                        {{ Form::hidden('loan_term_period', $loanType->loan_term_period) }}
                        
                        <!-- Display loan terms info for customer reference -->
                        <div class="form-group col-md-6 col-lg-6">
                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="fas fa-info-circle"></i> {{ __('Loan Terms') }}</h6>
                                <p class="mb-1"><strong>{{ __('Term:') }}</strong> {{ $loanType->max_loan_term }} {{ $loanType->loan_term_period }}</p>
                                <p class="mb-0"><strong>{{ __('Note:') }}</strong> {{ __('Loan terms are predefined for this loan type') }}</p>
                            </div>
                        </div>
                        
                        <div class="form-group col-md-12 col-lg-12">
                            {{ Form::label('purpose_of_loan', __('Purpose of Loan'), ['class' => 'form-label']) }}
                            {{ Form::textarea('purpose_of_loan', null, [
                                'class' => 'form-control', 
                                'rows' => '3', 
                                'placeholder' => __('Please describe the purpose of this loan'), 
                                'required' => 'required'
                            ]) }}
                        </div>
                        
                        <div class="form-group col-md-12 col-lg-12">
                            {{ Form::label('notes', __('Additional Notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('notes', null, [
                                'class' => 'form-control', 
                                'rows' => '2', 
                                'placeholder' => __('Any additional information (optional)')
                            ]) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mandatory Documents Section -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4>{{ __('Mandatory Documents') }}</h4>
                    <small class="text-muted text-danger">{{ __('These documents are required and must be uploaded') }}</small>
                </div>
                <div class="card-body">
                    <!-- Aadhaar Card Front and Back -->
                    <div class="row mb-3">
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('aadhaar_card_front', __('Aadhaar Card - Front Side'), ['class' => 'form-label required']) }}
                            {{ Form::file('aadhaar_card_front', ['class' => 'form-control', 'required' => 'required', 'accept' => '.jpg,.jpeg,.png,.pdf']) }}
                            <small class="text-muted">{{ __('Upload clear image/PDF of Aadhaar card front side') }}</small>
                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('aadhaar_card_back', __('Aadhaar Card - Back Side'), ['class' => 'form-label required']) }}
                            {{ Form::file('aadhaar_card_back', ['class' => 'form-control', 'required' => 'required', 'accept' => '.jpg,.jpeg,.png,.pdf']) }}
                            <small class="text-muted">{{ __('Upload clear image/PDF of Aadhaar card back side') }}</small>
                        </div>
                    </div>
                    
                    <!-- PAN Card -->
                    <div class="row mb-3">
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('pan_card', __('PAN Card'), ['class' => 'form-label required']) }}
                            {{ Form::file('pan_card', ['class' => 'form-control', 'required' => 'required', 'accept' => '.jpg,.jpeg,.png,.pdf']) }}
                            <small class="text-muted">{{ __('Upload clear image/PDF of PAN card') }}</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Documents Section -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4>{{ __('Additional Documents') }}</h4>
                    <small class="text-muted">{{ __('Upload any additional supporting documents (optional)') }}</small>
                </div>
                <div class="card-body">
                    <div class="row document_list">
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('document_type[]', __('Document Type'), ['class' => 'form-label']) }}
                            {!! Form::select('document_type[]', $documentTypes, null, [
                                'class' => 'form-control',
                                'placeholder' => __('Select Document Type')
                            ]) !!}
                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('document[]', __('Upload Document'), ['class' => 'form-label']) }}
                            {{ Form::file('document[]', ['class' => 'form-control', 'accept' => '.jpg,.jpeg,.png,.pdf']) }}
                        </div>
                        <div class="form-group col-md-2 col-lg-2">
                            <label class="form-label">&nbsp;</label><br>
                            <button type="button" class="btn btn-success btn-sm add_document">
                                <i data-feather="plus"></i> {{ __('Add More') }}
                            </button>
                        </div>
                    </div>
                    <div id="document_container"></div>
                </div>
            </div>
            
            <!-- Submit Section -->
            <div class="card mt-3">
                <div class="card-body text-center">
                    <div class="alert alert-info">
                        <i data-feather="info"></i>
                        <strong>{{ __('Important:') }}</strong> {{ __('Your loan application will be reviewed by our team. You will be notified of the decision via email and SMS.') }}
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i data-feather="send"></i> {{ __('Submit Loan Application') }}
                    </button>
                    <a href="{{ route('loan.index') }}" class="btn btn-secondary btn-lg ml-2">
                        <i data-feather="arrow-left"></i> {{ __('Back to Loan Types') }}
                    </a>
                </div>
            </div>
        </div>
        
        {{ Form::close() }}
    </div>
    
    <style>
        .loan-type-info {
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
        .form-label.required::after {
            content: ' *';
            color: #dc3545;
            font-weight: bold;
        }
        .text-danger {
            color: #dc3545 !important;
        }
    </style>
    
    <script>
        $(document).ready(function() {
            // Add more document functionality
            $('.add_document').click(function() {
                var documentHtml = `
                    <div class="row document_list mt-3">
                        <div class="form-group col-md-4 col-lg-4">
                            <select name="document_type[]" class="form-control" required>
                                <option value="">{{ __('Select Document Type') }}</option>
                                @foreach($documentTypes as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            <input type="file" name="document[]" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2 col-lg-2">
                            <button type="button" class="btn btn-danger btn-sm remove_document">
                                <i data-feather="minus"></i> {{ __('Remove') }}
                            </button>
                        </div>
                    </div>
                `;
                $('#document_container').append(documentHtml);
                feather.replace();
            });
            
            // Remove document functionality
            $(document).on('click', '.remove_document', function() {
                $(this).closest('.document_list').remove();
            });
        });
    </script>
@endsection
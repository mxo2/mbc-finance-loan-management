@extends('layouts.app')
@section('page-title')
    {{ __('Create Loan Cycle') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('loan-cycle.index') }}">
                {{ __('Loan Cycles') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Create') }}</a>
        </li>
    </ul>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Create Loan Cycle') }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => 'loan-cycle.store', 'method' => 'POST']) }}
                    <div class="row">
                        <div class="form-group col-md-6">
                            {{ Form::label('name', __('Cycle Name'), ['class' => 'form-label']) }}
                            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter cycle name')]) }}
                        </div>
                        
                        <div class="form-group col-md-6">
                            {{ Form::label('frequency', __('Frequency'), ['class' => 'form-label']) }}
                            {{ Form::select('frequency', \App\Models\LoanCycle::$frequencies, null, ['class' => 'form-control select2', 'required' => 'required', 'id' => 'frequency']) }}
                        </div>
                        
                        <div class="form-group col-md-6">
                            {{ Form::label('payment_day', __('Payment Day'), ['class' => 'form-label']) }}
                            {{ Form::number('payment_day', null, ['class' => 'form-control', 'required' => 'required', 'min' => '1', 'max' => '31', 'placeholder' => __('Enter payment day')]) }}
                            <small class="text-muted" id="payment-day-help">
                                {{ __('For monthly: 1-31 (day of month), For weekly: 1-7 (1=Monday, 7=Sunday)') }}
                            </small>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <div class="form-check form-switch mt-4">
                                {{ Form::checkbox('is_active', 1, true, ['class' => 'form-check-input', 'id' => 'is_active']) }}
                                {{ Form::label('is_active', __('Active'), ['class' => 'form-check-label']) }}
                            </div>
                        </div>
                        
                        <div class="form-group col-md-12">
                            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter description (optional)')]) }}
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="text-end">
                                <a href="{{ route('loan-cycle.index') }}" class="btn btn-secondary me-2">
                                    {{ __('Cancel') }}
                                </a>
                                {{ Form::submit(__('Create'), ['class' => 'btn btn-primary']) }}
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#frequency').on('change', function() {
            var frequency = $(this).val();
            var helpText = '';
            var maxDay = 31;
            
            if (frequency === 'monthly') {
                helpText = 'Enter day of month (1-31)';
                maxDay = 31;
            } else if (frequency === 'weekly') {
                helpText = 'Enter day of week (1=Monday, 7=Sunday)';
                maxDay = 7;
            } else if (frequency === 'daily') {
                helpText = 'Enter interval in days';
                maxDay = 365;
            } else if (frequency === 'yearly') {
                helpText = 'Enter day of year (1-365)';
                maxDay = 365;
            }
            
            $('#payment_day').attr('max', maxDay);
            $('#payment-day-help').text(helpText);
        });
    });
</script>
@endpush
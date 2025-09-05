@extends('layouts.app')
@section('page-title')
    {{ __('Loan Create') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('loan.index') }}">{{ __('Loan') }}</a>
    </li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Create') }}</a>
    </li>
@endsection
@section('content')
    <div class="row">
        {{ Form::open(['url' => 'loan', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
        <div class="col-xl-12 col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ loanPrefix() . $loanNumber }}</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('branch_id[]', __('Branch'), ['class' => 'form-label']) }}
                            {!! Form::select('branch_id', $branch, old('branch_id'), [
                                'class' => 'form-control select2 ',
                                'required' => 'required',
                            ]) !!}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('loan_type', __('Loan Type'), ['class' => 'form-label']) }}
                            {!! Form::select('loan_type', $loanTypes, old('loan_type'), [
                                'class' => 'form-control select2',
                                'required' => 'required',
                            ]) !!}
                        </div>
                        @if (\Auth::user()->type != 'customer')

                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('customer', __('Customer'), ['class' => 'form-label']) }}
                            {!! Form::select('customer', $customers, old('customer'), [
                                'class' => 'form-control select2',
                                'required' => 'required',
                            ]) !!}
                        </div>
                        @else
                            {{ Form::hidden('customer', \Auth::user()->id, ['class' => 'form-control']) }}
                        @endif

                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('loan_start_date', __('loan start date'), ['class' => 'form-label']) }}
                            {{ Form::date('loan_start_date', old('loan_start_date'), ['class' => 'form-control startDate', 'placeholder' => __('Enter loan start date'), 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('loan_terms', __('loan terms'), ['class' => 'form-label']) }}
                            {{ Form::number('loan_terms', old('loan_terms'), ['class' => 'form-control loanTerm', 'placeholder' => __('Enter loan terms'), 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('loan_term_period', __('loan term period'), ['class' => 'form-label']) }}
                            {!! Form::select('loan_term_period', $termPeroid, old('loan_term_period'), [
                                'class' => 'form-control termPeriod select2 ',
                                'required' => 'required',
                            ]) !!}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('loan_due_date', __('loan due date'), ['class' => 'form-label']) }}
                            {{ Form::date('loan_due_date', old('loan_due_date'), ['class' => 'form-control dueDate', 'placeholder' => __('Enter loan due date'), 'required' => 'required', 'readOnly']) }}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('amount', __('amount'), ['class' => 'form-label']) }}
                            {{ Form::number('amount', old('amount'), ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter amount'), 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('purpose_of_loan', __('purpose of loan'), ['class' => 'form-label']) }}
                            {{ Form::textarea('purpose_of_loan', old('purpose_of_loan'), ['class' => 'form-control', 'rows' => '1', 'placeholder' => __('Enter purpose of loan'), 'required' => 'required']) }}
                        </div>


                        <div class="form-group col-md-12 col-lg-12">
                            {{ Form::label('notes', __('notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('notes', old('notes'), ['class' => 'form-control', 'rows' => '1', 'placeholder' => __('Enter notes')]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-12 col-md-12 document">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Document') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row document_list">
                        <div class="form-group col-md-3 col-lg-3">
                            {{ Form::label('document_type[]', __('Document Type'), ['class' => 'form-label']) }}
                            {!! Form::select('document_type[]', $documentTypes, old('document_type'), [
                                'class' => 'form-control  ',
                            ]) !!}
                        </div>
                        <div class="form-group col-md-3 col-lg-3">
                            {{ Form::label('document[]', __('document'), ['class' => 'form-label']) }}
                            {{ Form::file('document[]', ['class' => 'form-control']) }}
                        </div>
                        <div class="form-group col-md-2 col-lg-2">
                            {{ Form::label('document_status[]', __('status'), ['class' => 'form-label']) }}
                            {!! Form::select('document_status[]', $document_status, old('document_status'), [
                                'class' => 'form-control  ',
                            ]) !!}
                        </div>
                        <div class="form-group col">
                            {{ Form::label('description[]', __('description'), ['class' => 'form-label']) }}
                            {{ Form::textarea('description[]', old('description'), ['class' => 'form-control', 'placeholder' => __('Enter description'), 'rows' => '1']) }}
                        </div>
                        <div class="col-auto m-auto">
                            <a href="javascript:void(0)"
                                class="text-danger location_list_remove btn btn-md document_list_remove "> <i
                                    class="ti ti-trash"></i></a>
                        </div>
                    </div>
                    <div class="document_list_results"></div>
                    <div class="row ">
                        <div class="col-sm-12">
                            <a href="javascript:void(0)" class="btn btn-secondary btn-xs document_clone "><i
                                    class="ti ti-circle-plus"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row ">
            <div class="form-group text-end">
                {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary ml-10']) }}
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endsection
@push('script-page')
    <script>
        function dateCalculation() {
            let startDate = $('.startDate').val();
            let loanTerm = $('.loanTerm').val() || 1;
            let termPeriod = $('.termPeriod').val() || 'months';

            if (!startDate) {
                $('.dueDate').val('');
                return;
            }

            loanTerm = parseInt(loanTerm, 10);

            let date = new Date(startDate);

            if (termPeriod === 'days') {
                date.setDate(date.getDate() + loanTerm);
            } else if (termPeriod === 'weeks') {
                date.setDate(date.getDate() + (loanTerm * 7));
            } else if (termPeriod === 'months') {
                date.setMonth(date.getMonth() + loanTerm);
            } else if (termPeriod === 'years') {
                date.setFullYear(date.getFullYear() + loanTerm);
            } else {
                console.warn("Invalid termPeriod:", termPeriod);
            }

            let year = date.getFullYear();
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let day = String(date.getDate()).padStart(2, '0');

            let formattedDate = `${year}-${month}-${day}`;

            $('.dueDate').val(formattedDate);
        }


        $('.startDate, .loanTerm, .termPeriod').on('change input', dateCalculation);



        $('.document').on('click', '.document_list_remove', function() {
            if ($('.document_list').length > 1) {
                $(this).parent().parent().remove();
            }
        });
        $('.document').on('click', '.document_clone', function() {
            var clonedCocument = $('.document_clone').closest('.document').find('.document_list').first().clone();
            clonedCocument.find('input[type="text"]').val('');
            $('.document_list_results').append(clonedCocument);
        });

        $('.document').on('click', '.document_list_remove', function() {
            var id = $(this).data('val');
        });
    </script>
@endpush

@extends('layouts.app')
@section('page-title')
    {{ __('Loan Edit') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('loan.index') }}">{{ __('Loan') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Edit') }}</a>
        </li>
    </ul>
@endsection
@section('content')
    <div class="row">
        {{ Form::model($loan, ['route' => ['loan.update', encrypt($loan->id)], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
        <div class="col-xl-12 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ loanPrefix() . $loan->loan_id }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                            {!! Form::select('branch_id', $branch, null, [
                                'class' => 'form-control  ',
                                'required' => 'required',
                            ]) !!}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('loan_type', __('Loan Type'), ['class' => 'form-label']) }}
                            {!! Form::select('loan_type', $loanTypes, null, [
                                'class' => 'form-control select2',
                                'required' => 'required',
                            ]) !!}
                        </div>
                        @if (\Auth::user()->type != 'customer')
                            <div class="form-group col-md-4 col-lg-4">
                                {{ Form::label('customer', __('Customer'), ['class' => 'form-label']) }}
                                {!! Form::select('customer', $customers, null, [
                                    'class' => 'form-control select2',
                                    'required' => 'required',
                                ]) !!}
                            </div>
                        @else
                            {{ Form::hidden('customer', \Auth::user()->id, ['class' => 'form-control']) }}
                        @endif
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('loan_start_date', __('loan start date'), ['class' => 'form-label']) }}
                            {{ Form::date('loan_start_date', null, ['class' => 'form-control', 'placeholder' => __('Enter loan start date'), 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('loan_due_date', __('loan due date'), ['class' => 'form-label']) }}
                            {{ Form::date('loan_due_date', null, ['class' => 'form-control', 'placeholder' => __('Enter loan due date'), 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('amount', __('amount'), ['class' => 'form-label']) }}
                            {{ Form::number('amount', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter amount'), 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('purpose_of_loan', __('purpose of loan'), ['class' => 'form-label']) }}
                            {{ Form::textarea('purpose_of_loan', null, ['class' => 'form-control', 'rows' => '1', 'placeholder' => __('Enter purpose of loan'), 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('loan_terms', __('loan terms'), ['class' => 'form-label']) }}
                            {{ Form::number('loan_terms', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter loan terms'), 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('loan_term_period', __('loan term period'), ['class' => 'form-label']) }}
                            {!! Form::select('loan_term_period', $termPeroid, null, [
                                'class' => 'form-control select2',
                                'required' => 'required',
                            ]) !!}
                        </div>
                        @if (\Auth::user()->type != 'customer')
                            <div class="form-group col-md-4 col-lg-4">
                                {{ Form::label('status', __('status'), ['class' => 'form-label']) }}
                                {!! Form::select('status', $status, null, [
                                    'class' => 'form-control select2',
                                    'required' => 'required',
                                ]) !!}
                            </div>
                        @else
                            {{ Form::hidden('status', $loan->status, ['class' => 'form-control']) }}
                        @endif
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('notes', __('notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => '1', 'placeholder' => __('Enter notes')]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-12 col-md-12 document">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Document Detail') }}</h4>
                </div>
                <div class="card-body">
                    @foreach ($loan->Documents as $item)
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
                        <div class="row document_list">
                            <div class="form-group col-md-3 col-lg-3">
                                {{ Form::label('document_type[]', __('Document Type'), ['class' => 'form-label']) }}
                                {!! Form::select('document_type[]', $documentTypes, $item->document_type, [
                                    'class' => 'form-control  ',
                                    'required' => 'required',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-3 col-lg-3">
                                {{ Form::label('document[]', __('document'), ['class' => 'form-label']) }}
                                {{ Form::file('document[]', ['class' => 'form-control']) }}
                            </div>
                            <div class="form-group col-md-2 col-lg-2">
                                {{ Form::label('document_status[]', __('status'), ['class' => 'form-label']) }}
                                {!! Form::select('document_status[]', $document_status, $item->status, [
                                    'class' => 'form-control  ',
                                    'required' => 'required',
                                ]) !!}
                            </div>
                            <div class="form-group col">
                                {{ Form::label('description[]', __('notes'), ['class' => 'form-label']) }}
                                {{ Form::textarea('description[]', $item->notes, ['class' => 'form-control', 'placeholder' => __('Enter notes'), 'rows' => '1']) }}
                            </div>
                            <div class="col-auto m-auto">
                                <a href="javascript:void(0)" class="fs-2 text-danger document_list_remove btn-sm "> <i
                                        class="ti ti-trash"></i></a>
                            </div>
                        </div>
                    @endforeach

                    @if (count($loan->Documents) == 0)
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
                                {{ Form::label('description[]', __('notes'), ['class' => 'form-label']) }}
                                {{ Form::textarea('description[]', old('description'), ['class' => 'form-control', 'placeholder' => __('Enter notes'), 'rows' => '1']) }}
                            </div>
                            <div class="col-auto m-auto">
                                <a href="javascript:void(0)" class="fs-2 text-danger document_list_remove btn-sm "> <i
                                        class="ti ti-trash"></i></a>
                            </div>
                        </div>
                    @endif
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
                {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary ml-10']) }}
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endsection
@push('script-page')
    <script>
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

@extends('layouts.app')
@section('page-title')
    {{ __('Customer Edit') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('customer.index') }}">{{ __('Customer') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Edit') }}</a>
        </li>
    </ul>
@endsection
@section('content')
    <div class="row">
        {{ Form::model($customer, ['route' => ['customer.update', encrypt($customer->id)], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
        <div class="row">
            <div class="col-xl-6 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ customerPrefix() . $customer->customer->customer_id }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="form-group col-md-12 col-lg-12">
                                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name'), 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                                {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Enter email'), 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('phone_number', __('Phone Number'), ['class' => 'form-label']) }}
                                {{ Form::text('phone_number', null, ['class' => 'form-control', 'placeholder' => __('Enter phone number'), 'required' => 'required']) }}
                                <small class="form-text text-muted">
                                    {{ __('Please enter the number with country code. e.g., +91XXXXXXXXXX') }}
                                </small>
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) }}
                                {{ Form::date('dob', !empty($customer->customer) ? $customer->customer->dob : null, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('gender', __('Gender'), ['class' => 'form-label']) }}
                                {!! Form::select('gender', $gender, !empty($customer->customer) ? $customer->customer->gender : null, [
                                    'class' => 'form-control select2',
                                    'required' => 'required',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('marital_status', __('Marital Status'), ['class' => 'form-label']) }}
                                {!! Form::select(
                                    'marital_status',
                                    $maritalStatus,
                                    !empty($customer->customer) ? $customer->customer->marital_status : null,
                                    ['class' => 'form-control select2', 'required' => 'required'],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                                {!! Form::select('branch_id', $branch, !empty($customer->customer) ? $customer->customer->branch_id : null, [
                                    'class' => 'form-control select2',
                                    'required' => 'required',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Additional Detail') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('profession', __('Profession'), ['class' => 'form-label']) }}
                                {{ Form::text('profession', !empty($customer->customer) ? $customer->customer->profession : null, ['class' => 'form-control', 'placeholder' => __('Enter profession'), 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('company', __('Company'), ['class' => 'form-label']) }}
                                {{ Form::text('company', !empty($customer->customer) ? $customer->customer->company : null, ['class' => 'form-control', 'placeholder' => __('Enter company'), 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                                {{ Form::text('city', !empty($customer->customer) ? $customer->customer->city : null, ['class' => 'form-control', 'placeholder' => __('Enter city'), 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
                                {{ Form::text('state', !empty($customer->customer) ? $customer->customer->state : null, ['class' => 'form-control', 'placeholder' => __('Enter state'), 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                                {{ Form::text('country', !empty($customer->customer) ? $customer->customer->country : null, ['class' => 'form-control', 'placeholder' => __('Enter country'), 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('zip_code', __('Zip Code'), ['class' => 'form-label']) }}
                                {{ Form::text('zip_code', !empty($customer->customer) ? $customer->customer->zip_code : null, ['class' => 'form-control', 'placeholder' => __('Enter zip code'), 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                                {{ Form::textarea('address', !empty($customer->customer) ? $customer->customer->address : null, ['class' => 'form-control', 'placeholder' => __('Enter address'), 'rows' => 1, 'required' => 'required']) }}
                            </div>

                            <div class="form-group col-md-6 col-lg-6">
                                {{ Form::label('notes', __('Note'), ['class' => 'form-label']) }}
                                {{ Form::textarea('notes', !empty($customer->customer) ? $customer->customer->notes : null, ['class' => 'form-control', 'placeholder' => __('Enter notes'), 'rows' => 1]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12 col-md-12 document">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Document Detail') }}</h4>
                    </div>

                    <div class="card-body">
                        @foreach ($customer->Documents as $item)
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                            <div class="row document_list">
                                <div class="form-group col-md-3 col-lg-3">
                                    {{ Form::label('document_type[]', __('Document Type'), ['class' => 'form-label']) }}
                                    {!! Form::select('document_type[]', $documentTypes, $item->document_type, [
                                        'class' => 'form-control type ',
                                        'required' => 'required',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-3 col-lg-3">
                                    {{ Form::label('document[]', __('document'), ['class' => 'form-label']) }}
                                    {{ Form::file('document[]', ['class' => 'form-control']) }}
                                    {!! Form::hidden('document_id[]', $item->id, ['class' => 'dasdasda']) !!}
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
                                    <a href="javascript:void(0)" class="fs-2 text-danger document_list_remove btn-sm ">
                                        <i data-feather="trash-2"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach

                        @if (count($customer->Documents) == 0)
                            <div class="row document_list">
                                <div class="form-group col-md-3 col-lg-3">
                                    {{ Form::label('document_type[]', __('Document Type'), ['class' => 'form-label']) }}
                                    {!! Form::select('document_type[]', $documentTypes, old('document_type'), [
                                        'class' => 'form-control type ',
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
            clonedCocument.find('.dasdasda').val('0');
            clonedCocument.find('.type').val('');
            $('.document_list_results').append(clonedCocument);
        });

        $('.document').on('click', '.document_list_remove', function() {
            var id = $(this).data('val');
        });
    </script>
@endpush

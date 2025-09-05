@extends('layouts.app')
@section('page-title')
    {{ customerPrefix() }}{{ $customer->customer->customer_id }} {{ __('Detail') }}
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
            <a href="#"> {{ customerPrefix() }}{{ $customer->customer->customer_id }} {{ __('Detail') }}</a>
        </li>
    </ul>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-6 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4> {{ customerPrefix() }}{{ $customer->customer->customer_id }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Branch') }}</b>
                                <p class="mb-20">
                                    {{ !empty($customer->customer) && !empty($customer->customer->branch) ? $customer->customer->branch->name : '-' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Name') }}</b>
                                <p class="mb-20">{{ $customer->name }} </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Email') }}</b>
                                <p class="mb-20">{{ $customer->email }} </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Phone Number') }}</b>
                                <p class="mb-20">
                                    <span> {{ $customer->phone_number }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Gender') }}</b>
                                <p class="mb-20">
                                    <span> {{ $customer->customer->gender }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Date of Birth') }}</b>
                                <p class="mb-20">
                                    <span> {{ dateFormat($customer->customer->dob) }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Marital Status') }}</b>
                                <p class="mb-20">
                                    <span> {{ $customer->customer->marital_status }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Additional Detail') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Profession') }}</b>
                                <p class="mb-20">
                                    <span> {{ $customer->customer->profession }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Company') }}</b>
                                <p class="mb-20">
                                    <span> {{ $customer->customer->company }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('City') }}</b>
                                <p class="mb-20">
                                    <span> {{ $customer->customer->city }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('State') }}</b>
                                <p class="mb-20">
                                    <span> {{ $customer->customer->state }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Country') }}</b>
                                <p class="mb-20">
                                    <span> {{ $customer->customer->country }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Zip Code') }}</b>
                                <p class="mb-20">
                                    <span> {{ $customer->customer->zip_code }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Address') }}</b>
                                <p class="mb-20">
                                    <span> {{ $customer->customer->address }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <b>{{ __('Notes') }}</b>
                                <p class="mb-20">
                                    <span> {{ $customer->customer->notes }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (count($customer->Documents) > 0)
        <div class="row">
            <div class="col-xl-12 col-md-12 ">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Document') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12 col-lg-12">
                            @foreach ($customer->Documents as $document)
                                <div class="row">
                                    <div class="col-md-3 col-lg-3">
                                        <div class="detail-group">
                                            <b>{{ __('Type') }}</b>
                                            <p class="mb-20">
                                                {{ !empty($document->types) ? $document->types->title : '-' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-lg-3">
                                        <div class="detail-group">
                                            <b>{{ __('Document') }}</b>
                                            <p> <a href="{{ !empty($document->document) ? fetch_file($document->document,'upload/customer_document/') : '#' }}"
                                                    target="_blank" class="mb-20">{{ $document->document }} </a></p>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-lg-3">
                                        <div class="detail-group">
                                            <b>{{ __('Status') }}</b>
                                            <p class="mb-20">{{ \App\Models\Loan::$document_status[$document->status] }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-lg-3">
                                        <div class="detail-group">
                                            <b>{{ __('Notes') }}</b>
                                            <p class="mb-20">
                                                <span> {{ $document->notes }}</span>
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

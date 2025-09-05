@extends('layouts.app')
@php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
@endphp
@section('page-title')
    {{ __('Customer') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{ __('Customer') }}
            </a>
        </li>
    </ul>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                {{ __('Customer') }}
                            </h5>
                        </div>
                        @if (Gate::check('create customer'))
                            <div class="col-auto">
                                <a class="btn btn-secondary" href="{{ route('customer.create') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Customer') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Phone Number') }}</th>
                                    <th>{{ __('Profession') }}</th>
                                    <th>{{ __('Company') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    @if (Gate::check('edit customer') || Gate::check('edit customer') || Gate::check('delete customer'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customers as $customer)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 wid-40">
                                                     <img class="img-radius img-fluid wid-40"
                                                        src="{{ empty($customer->profile) ? $profile : fetch_file($customer->profile, 'upload/profile/') }}"
                                                        alt="Customer image">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="mb-1">
                                                        {{ $customer->name }}

                                                    </h5>
                                                    <p class="text-muted f-12 mb-0">
                                                        {{ $customer->customer ? customerPrefix() . $customer->customer->id : '' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $customer->email }} </td>
                                        <td>{{ !empty($customer->phone_number) ? $customer->phone_number : '-' }} </td>
                                        <td>{{ !empty($customer->customer) ? $customer->customer->profession : '-' }} </td>
                                        <td>{{ !empty($customer->customer) ? $customer->customer->company : '-' }} </td>
                                        <td>{{ !empty($customer->customer) && !empty($customer->customer->branch) ? $customer->customer->branch->name : '-' }}
                                        </td>
                                        @if (Gate::check('edit customer') || Gate::check('edit customer') || Gate::check('delete customer'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['customer.destroy', $customer->id]]) !!}
                                                    @can('edit customer')
                                                        <a class="text-warning" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detail') }}"
                                                            href="{{ route('customer.show', \Illuminate\Support\Facades\Crypt::encrypt($customer->id)) }}">
                                                            <i data-feather="eye"></i></a>
                                                    @endcan
                                                    @can('edit customer')
                                                        <a class="text-success" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}"
                                                            href="{{ route('customer.edit', \Illuminate\Support\Facades\Crypt::encrypt($customer->id)) }}">
                                                            <i data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete customer')
                                                        <a class=" text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    @endcan
                                                    {!! Form::close() !!}
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

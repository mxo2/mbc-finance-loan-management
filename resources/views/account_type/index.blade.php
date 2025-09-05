@extends('layouts.app')
@section('page-title')
    {{ __('Account Type') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Account Type') }}</a>
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
                                {{ __('Account Type') }}
                            </h5>
                        </div>
                        @if (Gate::check('create account type'))
                            <div class="col-auto">
                                <a class="btn btn-secondary btn-sm customModal" href="#" data-size="md"
                                    data-url="{{ route('account-type.create') }}"
                                    data-title="{{ __('Create Account Type') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Account Type') }}
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
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Interest Rate') }}</th>
                                    <th>{{ __('Interest Duration') }}</th>
                                    <th>{{ __('Min Maintain Amt') }}</th>
                                    <th>{{ __('Maintenance Charge') }}</th>
                                    <th>{{ __('Charge Deduct Month') }}</th>
                                    @if (Gate::check('edit account type') || Gate::check('delete account type'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accountTypes as $accountType)
                                    <tr>
                                        <td>{{ $accountType->title }} </td>
                                        <td>{{ $accountType->interest_rate }} </td>
                                        <td>{{ $accountType->interest_duration }} </td>
                                        <td>{{ priceFormat($accountType->min_maintain_amount) }} </td>
                                        <td>{{ priceFormat($accountType->maintenance_charges) }} </td>
                                        <td>{{ $accountType->charges_deduct_month }} </td>
                                        @if (Gate::check('edit account type') || Gate::check('delete account type'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['account-type.destroy', encrypt($accountType->id)]]) !!}
                                                    @if (Gate::check('edit account type'))
                                                        <a class="text-success customModal" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-url="{{ route('account-type.edit', encrypt($accountType->id)) }}"
                                                            data-title="{{ __('Edit account Type') }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endif
                                                    @if (Gate::check('delete account type'))
                                                        <a class=" text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    @endif
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

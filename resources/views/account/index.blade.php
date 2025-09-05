@extends('layouts.app')
@section('page-title')
    {{ __('Account') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Account') }}</a>
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
                                {{ __('Account') }}
                            </h5>
                        </div>
                        @if (Gate::check('create account'))
                            <div class="col-auto">
                                <a class="btn btn-secondary btn-sm customModal" href="#" data-size="md"
                                    data-url="{{ route('account.create') }}" data-title="{{ __('Create Account') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Account') }}
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
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Account Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                    @if (Gate::check('edit account') || Gate::check('delete account'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $account)
                                    <tr>
                                        <td>{{ accountPrefix() . $account->account_number }} </td>
                                        <td>{{ $account->Customers->name }} </td>
                                        <td>{{ $account->accountType->title }} </td>
                                        <td>{{ $account->status }} </td>
                                        <td>{{ priceFormat($account->balance) }} </td>
                                        @if (Gate::check('edit account') || Gate::check('delete account'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['account.destroy', encrypt($account->id)]]) !!}
                                                    @if (Gate::check('edit account'))
                                                        <a class="text-success customModal" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-url="{{ route('account.edit', encrypt($account->id)) }}"
                                                            data-title="{{ __('Edit Account') }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endif
                                                    @if (Gate::check('delete account'))
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

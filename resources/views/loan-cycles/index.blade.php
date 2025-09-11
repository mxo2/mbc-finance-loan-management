@extends('layouts.app')
@section('page-title')
    {{ __('Loan Cycles') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Loan Cycles') }}</a>
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
                                {{ __('Loan Cycles') }}
                            </h5>
                        </div>
                        @if (Gate::check('create loan cycle'))
                            <div class="col-auto">
                                <a href="{{ route('loan-cycle.create') }}" class="btn btn-primary">
                                    <i data-feather="plus"></i> {{ __('Create Loan Cycle') }}
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
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Frequency') }}</th>
                                    <th>{{ __('Payment Day') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    @if (Gate::check('edit loan cycle') || Gate::check('delete loan cycle'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cycles as $cycle)
                                    <tr>
                                        <td>{{ $cycle->name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $cycle->frequency_label }}</span>
                                        </td>
                                        <td>
                                            @if($cycle->frequency == 'monthly')
                                                {{ __('Day') }} {{ $cycle->payment_day }}
                                            @elseif($cycle->frequency == 'weekly')
                                                {{ __('Day') }} {{ $cycle->payment_day }} {{ __('of week') }}
                                            @else
                                                {{ $cycle->payment_day }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($cycle->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $cycle->description ?? '-' }}</td>
                                        @if (Gate::check('edit loan cycle') || Gate::check('delete loan cycle'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['loan-cycle.destroy', encrypt($cycle->id)]]) !!}
                                                    @if (Gate::check('edit loan cycle'))
                                                        <a href="{{ route('loan-cycle.edit', encrypt($cycle->id)) }}"
                                                            class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i data-feather="edit"></i>
                                                        </a>
                                                    @endif
                                                    @if (Gate::check('delete loan cycle'))
                                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                            data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"
                                                            onclick="return confirm('{{ __('Are you sure?') }}')">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
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
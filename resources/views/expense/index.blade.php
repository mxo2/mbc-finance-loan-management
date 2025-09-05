@extends('layouts.app')
@section('page-title')
    {{ __('Expense') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Expense') }}</a>
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
                                {{ __('Expense') }}
                            </h5>
                        </div>
                        @if (Gate::check('create expense'))
                            <div class="col-auto">
                                <a class="btn btn-secondary btn-sm customModal" href="#" data-size="md"
                                    data-url="{{ route('expense.create') }}" data-title="{{ __('Create Expense') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Expense') }}
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
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Attachment') }}</th>
                                    <th>{{ __('Note') }}</th>
                                    @if (Gate::check('edit expense') || Gate::check('delete expense'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expenses as $expense)
                                    <tr>
                                        <td>{{ $expense->title }} </td>
                                        <td>{{ dateFormat($expense->date) }} </td>
                                        <td>{{ priceFormat($expense->amount) }} </td>
                                        <td>
                                            @if ($expense->attachment)
                                                <a href="{{ Storage::url('upload/expense/' . $expense->attachment) }}"
                                                    target="_blank"><i data-feather="file"></i></a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ !empty($expense->notes) ? $expense->notes : '-' }} </td>
                                        @if (Gate::check('edit expense') || Gate::check('delete expense'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['expense.destroy', encrypt($expense->id)]]) !!}
                                                    @if (Gate::check('edit expense'))
                                                        <a class="text-success customModal" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-url="{{ route('expense.edit', encrypt($expense->id)) }}"
                                                            data-title="{{ __('Edit expense') }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endif
                                                    @if (Gate::check('delete expense'))
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

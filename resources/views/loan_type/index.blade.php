@extends('layouts.app')
@section('page-title')
    {{ __('Loan Type') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Loan Type') }}</a>
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
                                {{ __('Loan Type') }}
                            </h5>
                        </div>
                        @if (Gate::check('create loan type'))
                            <div class="col-auto">
                                <a class="btn btn-secondary btn-sm customModal" href="#" data-size="lg"
                                    data-url="{{ route('loan-type.create') }}" data-title="{{ __('Create Loan Type') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Loan Type') }}
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
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Loan Eligible Amount') }}</th>
                                    <th>{{ __('Loan Interest Detail') }}</th>
                                    <th>{{ __('Loan Terms Detail') }}</th>
                                    <th>{{ __('Loan Payment Penalty') }}</th>
                                    <th>{{ __('File Charges') }}</th>
                                    @if (Gate::check('edit loan type') || Gate::check('delete loan type'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($loanTypes as $loanType)
                                    <tr>
                                        <td>{{ $loanType->type }} </td>
                                        <td>
                                            {{ __('Min Amount') }} : {{ priceFormat($loanType->min_loan_amount) }} <br>
                                            {{ __('Max Amount') }} : {{ priceFormat($loanType->max_loan_amount) }}
                                        </td>
                                        <td>
                                            {{ __('Interest Type') }} :
                                            {{ \App\Models\LoanType::$interestType[$loanType->interest_type] ?? $loanType->interest_type }} <br>
                                            {{ __('Interest Rate') }} :
                                            {{ $loanType->interest_rate . '% / ' . __('Year') }}
                                        </td>
                                        <td>
                                            {{ __('Loan Term Limit') }} : {{ $loanType->max_loan_term }}
                                            {{ $loanType->loan_term_period }}
                                        </td>
                                        <td>
                                            @if($loanType->penalty_type === 'percentage')
                                                {{ $loanType->penalties }}%
                                            @else
                                                ₹{{ number_format($loanType->penalties) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($loanType->file_charges && $loanType->file_charges > 0)
                                                @if($loanType->file_charges_type === 'percentage')
                                                    {{ $loanType->file_charges }}%
                                                @else
                                                    ₹{{ number_format($loanType->file_charges) }}
                                                @endif
                                            @else
                                                <span class="text-muted">{{ __('No charges') }}</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('edit loan type') || Gate::check('delete loan type'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['loan-type.destroy', encrypt($loanType->id)]]) !!}
                                                    @if (Gate::check('edit loan type'))
                                                        <a class="text-success customModal" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" data-size="lg"
                                                            href="#"
                                                            data-url="{{ route('loan-type.edit', encrypt($loanType->id)) }}"
                                                            data-title="{{ __('Edit Loan Type') }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endif
                                                    @if (Gate::check('delete loan type'))
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

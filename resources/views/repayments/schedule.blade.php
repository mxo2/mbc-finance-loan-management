@extends('layouts.app')
@section('page-title')
    {{ __('Repayment schedule') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Repayment schedule') }}</a>
        </li>
    </ul>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    {{ Form::open(['route' => 'schedule.filetr', 'method' => 'get', 'enctype' => 'multipart/form-data']) }}
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                {{ __('Repayment schedule') }}
                            </h5>
                        </div>

                        <div class="col-2">
                            {!! Form::select('loan', $loans, !empty($loanID) ? $loanID : null, ['class' => 'form-control select2 loan_id']) !!}
                        </div>

                        <div class="col-2">
                            {{ Form::text('date', !empty($dateRange) ? $dateRange : null, ['class' => 'form-control', 'placeholder' => __('Select date'), 'id' => 'demo', 'autocomplete' => 'off']) }}
                        </div>

                        <div class="col-1">
                            {!! Form::submit('Search', ['class' => 'btn btn-secondary subbtn']) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Loan No.') }}</th>
                                    <th>{{ __('Payment Date') }}</th>
                                    <th>{{ __('Interest') }}</th>
                                    <th>{{ __('Principal amount') }}</th>
                                    <th>{{ __('Penality') }}</th>
                                    <th>{{ __('Total Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Gate::check('delete repayment schedule') || Gate::check('repayment schedule payment'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schedules as $schedule)
                                    <tr>
                                        <td>{{ $schedule->Loans ? loanPrefix() . $schedule->Loans->loan_id : 0 }} </td>
                                        <td>{{ dateFormat($schedule->due_date) }} </td>
                                        <td>{{ priceFormat($schedule->interest) }} </td>
                                        <td>{{ priceFormat($schedule->installment_amount) }} </td>
                                        <td>{{ $schedule->penality ? priceFormat($schedule->penality) : 0.0 }} </td>
                                        <td>{{ priceFormat($schedule->total_amount) }} </td>
                                        <td>
                                            @if ($schedule->status == 'Pending')
                                                <span class="badge text-bg-warning">{{ $schedule->status }}</span>
                                            @else
                                                <span class="badge text-bg-success">{{ $schedule->status }}</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('delete repayment schedule') || Gate::check('repayment schedule payment'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['repayment.schedules.destroy', encrypt($schedule->id)]]) !!}
                                                    @if (Gate::check('delete repayment schedule'))
                                                        <a class=" text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    @endif

                                                    @if (Gate::check('repayment schedule payment'))
                                                        <a class="text-success" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Make Payemnt') }}"
                                                            href="{{ route('schedule.payment', encrypt($schedule->id)) }}">
                                                            <i data-feather="eye"></i>
                                                        </a>
                                                    @endif
                                                    @if (
                                                        \Auth::user()->type == 'owner' &&
                                                            !empty($schedule->payment_type) &&
                                                            $schedule->payment_type == 'Bank Transfer' &&
                                                            $schedule->status == 'In Process')
                                                        <a class="text-warning customModal" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Payemnt Accept & Reject') }}"
                                                            href="#" data-title="{{ __('Payemnt Accept & Reject') }}"
                                                            data-url="{{ route('schedule.payment.ap', encrypt($schedule->id)) }}">
                                                            <i data-feather="check-square"></i>
                                                        </a>
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

@push('css-page')
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker/daterangepicker.css') }}" />
@endpush
@push('script-page')
    <script src="{{ asset('assets/js/daterangepicker/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/daterangepicker/daterangepicker.min.js') }}"></script>

    <script>
        var today = new Date();
        var day = String(today.getDate()).padStart(2, '0');
        var month = String(today.getMonth() + 1).padStart(2, '0');
        var year = today.getFullYear();

        var formattedDate = day + '-' + month + '-' + year;

        $('#demo').daterangepicker({
            autoApply: true,
            autoUpdateInput: false,
            locale: {
                format: 'MM/DD/YYYY'
            }
        }, function(start, end) {
            var start_date = start.format('MM/DD/YYYY');
            var end_date = end.format('MM/DD/YYYY');
            $('#demo').val(start_date + ' - ' + end_date);
        });
    </script>
@endpush

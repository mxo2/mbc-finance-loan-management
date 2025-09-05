@extends('layouts.app')
@section('page-title')
    {{ __('Loans') }}
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
            <a href="#">{{ loanPrefix() . $loan->loan_id }}</a>
        </li>
    </ul>
@endsection



@push('script-page')
    <script src="{{ asset('assets/js/vendors/ckeditor/ckeditor.js') }}"></script>
    <script>
        setTimeout(() => {
            feather.replace();
        }, 500);
    </script>
@endpush

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ loanPrefix() . $loan->loan_id }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <h6>{{ __('Loan ID') }}</h6>
                                <p class="mb-20">
                                    {{ loanPrefix() . $loan->loan_id }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <h6>{{ __('Loan Type') }}</h6>
                                <p class="mb-20">
                                    {{ $loan->loanType ? $loan->loanType->type : '' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <h6>{{ __('Customer') }}</h6>
                                <p class="mb-20">
                                    {{ $loan->Customers ? $loan->Customers->name : '' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <h6>{{ __('Start Date') }}</h6>
                                <p class="mb-20">
                                    {{ dateFormat($loan->loan_start_date) }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <h6>{{ __('End Date') }}</h6>
                                <p class="mb-20">
                                    {{ dateFormat($loan->loan_due_date) }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <h6>{{ __('Amount') }}</h6>
                                <p class="mb-20">
                                    {{ priceFormat($loan->amount) }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <h6>{{ __('Purpose of loan') }}</h6>
                                <p class="mb-20">
                                    {{ $loan->purpose_of_loan }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <div class="detail-group">
                                <h6>{{ __('Status') }}</h6>
                                <p class="mb-20">
                                    @if ($loan->status == 'draft')
                                        <span
                                            class="d-inline badge text-bg-info">{{ \App\Models\Loan::$status[$loan->status] }}</span>
                                    @elseif($loan->status == 'submitted')
                                        <span
                                            class="d-inline badge text-bg-primary">{{ \App\Models\Loan::$status[$loan->status] }}</span>
                                    @elseif($loan->status == 'under_review')
                                        <span
                                            class="d-inline badge text-bg-warning">{{ \App\Models\Loan::$status[$loan->status] }}</span>
                                    @elseif($loan->status == 'approved')
                                        <span
                                            class="d-inline badge text-bg-success">{{ \App\Models\Loan::$status[$loan->status] }}</span>
                                    @elseif($loan->status == 'rejected')
                                        <span
                                            class="d-inline badge text-bg-danger">{{ \App\Models\Loan::$status[$loan->status] }}</span>
                                    @else
                                        <span
                                            class="d-inline badge text-bg-info">{{ \App\Models\Loan::$status[$loan->status] }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Section -->
            @if (count($loan->Documents) > 0)
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Document') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12 col-lg-12">
                            @foreach ($loan->Documents as $document)
                                <div class="row">
                                    <div class="col-md-3 col-lg-3">
                                        <div class="detail-group">
                                            <h6>{{ __('Type') }}</h6>
                                            <p class="mb-20">
                                                {{ !empty($document->types) ? $document->types->title : '-' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-lg-3">
                                        <div class="detail-group">
                                            <h6>{{ __('Document') }}</h6>
                                            <a href="{{ !empty($document->document) ? fetch_file($document->document,'upload/loan_document/') : '#' }}"
                                                target="_blank" class="mb-20">{{ $document->document }} </a>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-lg-3">
                                        <div class="detail-group">
                                            <h6>{{ __('Status') }}</h6>
                                            <p class="mb-20">{{ \App\Models\Loan::$document_status[$document->status] }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-lg-3">
                                        <div class="detail-group">
                                            <h6>{{ __('Notes') }}</h6>
                                            <p class="mb-20">
                                                <span>{{ $document->notes }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Repayment Schedules Section -->

            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                {{ __('Repayment Schedules') }}
                            </h5>
                        </div>

                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Payment Date') }}</th>
                                    <th>{{ __('Principal amount') }}</th>
                                    <th>{{ __('Interest') }}</th>
                                    <th>{{ __('Penality') }}</th>
                                    <th>{{ __('Total Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Gate::check('delete repayment schedule') || Gate::check('payment reminder'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($loan->RepaymentSchedules as $schedule)
                                    <tr>
                                        <td>{{ dateFormat($schedule->due_date) }} </td>
                                        <td>{{ priceFormat($schedule->installment_amount) }} </td>
                                        <td>{{ priceFormat($schedule->interest) }} </td>
                                         <td>{{ $schedule->penality ? priceFormat($schedule->penality) : 0.0 }} </td>
                                        <td>{{ priceFormat($schedule->total_amount) }} </td>
                                        <td>
                                            @if ($schedule->status == 'Pending')
                                                <span class="d-inline badge text-bg-warning">{{ $schedule->status }}</span>
                                            @else
                                                <span class="d-inline badge text-bg-success">{{ $schedule->status }}</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('delete repayment schedule') || Gate::check('payment reminder'))
                                            <td>
                                                <div class="cart-action">
                                                    @if ($schedule->status == 'Paid')
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['repayment.schedules.destroy', encrypt($schedule->id)]]) !!}
                                                        @if (Gate::check('delete repayment schedule'))
                                                            <a class="text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Delete') }}"
                                                                href="#"> <i data-feather="trash-2"></i>
                                                            </a>
                                                        @endif
                                                        {!! Form::close() !!}
                                                    @endif
                                                    @if ($schedule->status == 'Pending')
                                                        @if (Gate::check('payment reminder'))
                                                            <a class="text-success customModal" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Payment Reminder') }}"
                                                                data-url="{{ route('payment.reminder', encrypt($schedule->id)) }}"
                                                                data-size='lg' href="#">
                                                                <i data-feather="bell"></i>
                                                            </a>
                                                        @endif
                                                    @endif
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

            <!-- Repayments Section -->
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                {{ __('Repayments') }}
                            </h5>
                        </div>

                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">

                            <thead>
                                <tr>
                                    <th>{{ __('Payment Date') }}</th>
                                    <th>{{ __('Principal amount') }}</th>
                                    <th>{{ __('Interest') }}</th>
                                    <th>{{ __('Penality') }}</th>
                                    <th>{{ __('Total Amount') }}</th>
                                    @if (Gate::check('edit repayment') || Gate::check('delete repayment'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($loan->Repayments as $repayment)
                                    <tr>
                                        <td>{{ dateFormat($repayment->payment_date) }} </td>
                                        <td>{{ priceFormat($repayment->principal_amount) }} </td>
                                        <td>{{ priceFormat($repayment->interest) }} </td>
                                        <td>{{ $repayment->penality ? priceFormat($repayment->penality) : 0.0 }} </td>
                                        <td>{{ priceFormat($repayment->total_amount) }} </td>
                                        @if (Gate::check('edit repayment') || Gate::check('delete repayment'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['repayment.destroy', encrypt($repayment->id)]]) !!}
                                                    @if (Gate::check('edit repayment'))
                                                        <a class="text-success customModal" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-url="{{ route('repayment.edit', encrypt($repayment->id)) }}"
                                                            data-title="{{ __('Edit repayment') }}"> <i
                                                                data-feather="edit"></i>
                                                        </a>
                                                    @endif
                                                    @if (Gate::check('delete repayment'))
                                                        <a class="text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Delete') }}" href="#">
                                                            <i data-feather="trash-2"></i>
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

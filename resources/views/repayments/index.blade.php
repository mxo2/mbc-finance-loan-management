@extends('layouts.app')
@section('page-title')
    {{ __('Repayment') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('repayment') }}</a>
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
                                {{ __('Repayment') }}
                            </h5>
                        </div>
                        @if (Gate::check('create repayment'))
                            <div class="col-auto">
                                <a class="btn btn-secondary btn-sm customModal" href="#" data-size="md"
                                    data-url="{{ route('repayment.create') }}" data-title="{{ __('Create Repayment') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Repayment') }}
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
                                    <th>{{ __('Loan') }}</th>
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
                                @foreach ($repayments as $repayment)
                                    <tr>
                                        <td>{{ loanPrefix() . $repayment->Loans->loan_id }} </td>
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
                                                                data-feather="edit"></i></a>
                                                    @endif
                                                    @if (Gate::check('delete repayment'))
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
@push('script-page')
    <script>
        $(document).on("change", "#loan_id", function(e) {
            var csrf = $("meta[name=csrf-token]").attr("content");
            var value = $(this).val();
            var action = $(this).data("url");
            $.ajax({
                type: "POST",
                url: "{{ route('loan.installment') }}",
                data: {
                    _token: csrf,
                    loan: value,
                },
                success: function(response) {
                    if (response.status) {
                        data = response.installment;
                        $('input[name = "principal_amount"]').val(data.installment_amount);
                        $('input[name = "interest"]').val(data.interest);
                        $('input[name = "penality"]').val(data.penality);
                        $('input[name = "total_amount"]').val(data.total_amount);
                        $('input[name = "payment_date"]').val(data.due_date);
                        $('input[name = "schedule_id"]').val(data.id);
                    } else {
                        $('input[name = "principal_amount"]').val('');
                        $('input[name = "payment_date"]').val('');
                        $('input[name = "interest"]').val('');
                        $('input[name = "penality"]').val('');
                        $('input[name = "total_amount"]').val('');
                        $('input[name = "schedule_id"]').val('');
                    }
                },
            });
        });
    </script>
@endpush

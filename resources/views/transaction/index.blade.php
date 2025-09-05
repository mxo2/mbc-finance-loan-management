@extends('layouts.app')
@section('page-title')
    {{ __('Transaction') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Transaction') }}</a>
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
                                {{ __('Transaction') }}
                            </h5>
                        </div>
                        @if (Gate::check('create transaction'))
                            <div class="col-auto">
                                <a class="btn btn-secondary btn-sm customModal" href="#" data-size="md"
                                    data-url="{{ route('transaction.create') }}"
                                    data-title="{{ __('Create Transaction') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i>
                                    {{ __('Create Transaction') }}
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
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Date/Time') }}</th>
                                    @if (Gate::check('edit transaction') || Gate::check('delete transaction'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->account_number }} </td>
                                        <td>{{ $transaction->Customers->name }} </td>
                                        <td>{{ $transaction->type }} </td>
                                        <td>{{ $transaction->status }} </td>
                                        <td>{{ priceFormat($transaction->amount) }} </td>
                                        <td>{{ dateFormat($transaction->date_time) . ' ' . timeFormat($transaction->date_time) }}
                                        </td>
                                        @if (Gate::check('edit transaction') || Gate::check('delete transaction'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['transaction.destroy', encrypt($transaction->id)]]) !!}
                                                    @if (Gate::check('edit transaction'))
                                                        <a class="text-success customModal" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-url="{{ route('transaction.edit', encrypt($transaction->id)) }}"
                                                            data-title="{{ __('Edit transaction') }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endif
                                                    @if (Gate::check('delete transaction'))
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
        $(document).on("change", "#customer", function(e) {
            var csrf = $("meta[name=csrf-token]").attr("content");
            var value = $(this).val();
            var action = $(this).data("url");
            $.ajax({
                type: "POST",
                url: "{{ route('customer.account') }}",
                data: {
                    _token: csrf,
                    customer: value,
                },
                success: function(response) {
                    console.log(response)
                    if (response.status) {
                        $('#account_number').val(response.account);
                        $('#account').val(response.account_id);
                    }
                },
            });
        });
    </script>
@endpush

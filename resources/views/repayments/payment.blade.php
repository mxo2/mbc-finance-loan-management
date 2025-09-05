@extends('layouts.app')
@section('page-title')
    {{ __('Schedule Payment') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('repayment.schedules') }}">
                {{ __('Repayment schedule') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            {{ !empty($schedules->Loans) ? loanPrefix() . $schedules->Loans->loan_id : '' }}
        </li>
    </ul>
@endsection



{{-- @push('script-page')
    <script src="{{ asset('assets/js/plugins/ckeditor/classic/ckeditor.js') }}"></script>
    <script>
        setTimeout(() => {
            feather.replace();
        }, 500);
    </script>
@endpush --}}


@push('script-page')
    <script>
        $(document).on('click', '.print', function() {
            var printContents = document.getElementById('invoice-print').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;

        });
    </script>

    <script src="https://js.stripe.com/v3/"></script>

    <script type="text/javascript">
        @if (
            $schedulePaymentSettings['STRIPE_PAYMENT'] == 'on' &&
                !empty($schedulePaymentSettings['STRIPE_KEY']) &&
                !empty($schedulePaymentSettings['STRIPE_SECRET']))
            var stripe_key = Stripe('{{ $schedulePaymentSettings['STRIPE_KEY'] }}');
            var stripe_elements = stripe_key.elements();
            var strip_css = {
                base: {
                    fontSize: '14px',
                    color: '#32325d',
                },
            };
            var stripe_card = stripe_elements.create('card', {
                style: strip_css
            });
            stripe_card.mount('#card-element');

            var stripe_form = document.getElementById('stripe-payment');
            stripe_form.addEventListener('submit', function(event) {
                event.preventDefault();

                var billingDetails = {
                    line1: document.querySelector('[name="state"]')?.value || '',
                    city: document.querySelector('[name="city"]')?.value || '',
                    postal_code: document.querySelector('[name="zipcode"]')?.value || '',
                    country: document.querySelector('[name="country"]')?.value || ''
                };
                stripe_key.createToken(stripe_card).then(function(result) {
                    if (result.error) {
                        $("#stripe_card_errors").html(result.error.message);
                        $.NotificationApp.send("Error", result.error.message, "top-right",
                            "rgba(0,0,0,0.2)", "error");
                    } else {
                        var token = result.token;
                        var stripeForm = document.getElementById('stripe-payment');
                        var stripeHiddenData = document.createElement('input');
                        stripeHiddenData.setAttribute('type', 'hidden');
                        stripeHiddenData.setAttribute('name', 'stripeToken');
                        stripeHiddenData.setAttribute('value', token.id);
                        stripeForm.appendChild(stripeHiddenData);
                        stripeForm.submit();
                    }
                });
            });
        @endif
    </script>

    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>

    <script>
        $(document).on("click", "#flutterwavePaymentBtn", function() {
            var amount = $('.amount').val().trim();
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            var tx_ref = "RX1_" + Math.floor((Math.random() * 1000000000) + 1);
            var customer_email = '{{ \Auth::user()->email }}';
            var customer_name = '{{ \Auth::user()->name }}';
            var flutterwave_public_key = '{{ $schedulePaymentSettings['flutterwave_public_key'] }}';
            var currency = '{{ $schedulePaymentSettings['CURRENCY'] }}';

            var flutterwavePayment = getpaidSetup({
                txref: tx_ref,
                PBFPubKey: flutterwave_public_key,
                amount: amount, // Ensure amount is passed
                currency: currency,
                customer_email: customer_email,
                customer_name: customer_name,
                meta: [{
                    metaname: "payment_id",
                    metavalue: "id"
                }],
                onclose: function() {},
                callback: function(result) {
                    if (result.tx.chargeResponseCode == "00" || result.tx.chargeResponseCode == "0") {
                        var txRef = result.tx.txRef;
                        var redirectUrl =
                            "{{ url('invoice/flutterwave') }}/{{ \Illuminate\Support\Facades\Crypt::encrypt($schedules->id) }}/" +
                            txRef + "?amount=" + amount;
                        window.location.href = redirectUrl;
                    } else {
                        alert('Payment failed');
                    }
                    flutterwavePayment.close();
                }
            });
        });
    </script>

    <script src="{{ asset('assets/js/plugins/jquery.form.min.js') }}"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>

    @if (isset($schedulePaymentSettings['paystack_payment']) && $schedulePaymentSettings['paystack_payment'] == 'on')
        <script>
            $(document).on("click", "#paystackPaymentBtn", function() {

                $('#paystack-payment-form').ajaxForm(function(res) {
                    if (res.flag == 1) {
                        var paystack_callback = "{{ url('/invoice/paystack') }}";
                        var order_id = '{{ time() }}';
                        var coupon_id = res.coupon;
                        var handler = PaystackPop.setup({
                            key: '{{ $schedulePaymentSettings['paystack_public_key'] }}',
                            email: res.email,
                            amount: res.total_price * 100,
                            currency: res.currency,
                            ref: 'pay_ref_id' + Math.floor((Math.random() * 1000000000) +
                                1
                            ), // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
                            metadata: {
                                custom_fields: [{
                                    display_name: "Email",
                                    variable_name: "email",
                                    value: res.email,
                                }]
                            },

                            callback: function(response) {
                                window.location.href = paystack_callback + '/' + response
                                    .reference + '/' + '{{ encrypt($schedules->id) }}' +
                                    '?coupon_id=' + coupon_id;
                            },
                            onClose: function() {
                                alert('window closed');
                            }
                        });
                        handler.openIframe();
                        console.log(handler);
                    } else if (res.flag == 2) {

                    } else {
                        show_toastr('Error', data.message, 'msg');
                    }

                }).submit();
            });
        </script>
    @endif

@endpush


@php
    $main_logo = getSettingsValByName('company_logo');
@endphp


@section('content')
    <div class="row" id="invoice-print">
        <div class="col-sm-12">
            <div class="d-print-none card mb-3">
                <div class="card-body p-3">
                    <ul class="list-inline ms-auto mb-0 d-flex justify-content-end flex-wrap">


                        <li class="list-inline-item align-bottom me-2">
                            <a href="#" class="avtar avtar-s btn-link-secondary print" data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('Download') }}">
                                <i class="ph-duotone ph-printer f-22"></i>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>

            <div class="row">
                <div
                    class="{{ !in_array($schedules->status, ['Paid', 'In Process']) && \Auth::user()->type == 'customer' ? 'col-lg-8 col-md-12' : 'col-lg-12 col-md-12' }}">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="row align-items-center g-3">
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-center mb-2 navbar-brand img-fluid invoice-logo">
                                                <img src="{{ !empty($main_logo) ? fetch_file($main_logo, 'upload/logo/') : '#' }}"
                                                    class="img-fluid brand-logo" alt="images" />
                                            </div>
                                            <p class="mb-0">
                                                {{ !empty($schedules->Loans) ? loanPrefix() . $schedules->Loans->loan_id : '' }}
                                            </p>
                                        </div>
                                        <div class="col-sm-6 text-sm-end">
                                            <h6>
                                                {{ __('Due Date') }}
                                                <span
                                                    class="text-muted f-w-400">{{ isset($schedules->due_date) ? dateFormat($schedules->due_date) : '' }}</span>
                                            </h6>

                                            <h6>
                                                {{ __('Status') }}
                                                <span class="text-muted f-w-400">
                                                    @if (isset($schedules->status))
                                                        @if ($schedules->status == 'Paid')
                                                            <span
                                                                class="badge text-bg-success">{{ $schedules->status }}</span>
                                                        @else
                                                            <span
                                                                class="badge text-bg-danger">{{ $schedules->status }}</span>
                                                        @endif
                                                    @endif
                                                </span>
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="border rounded p-3">
                                        <h6 class="mb-0">From:</h6>

                                        <h5>{{ $settings['company_name'] }}</h5>
                                        <p class="mb-0">{{ $settings['company_phone'] }}</p>
                                        <p class="mb-0">{{ $settings['company_email'] }}</p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="border rounded p-3">
                                        <h6 class="mb-0">To:</h6>
                                        <h5>{{ !empty($schedules->Loans) && !empty($schedules->Loans) ? ($schedules->Loans->Customers ? $schedules->Loans->Customers->name : '') : '' }}
                                        </h5>
                                        <p class="mb-0">
                                            {{ !empty($schedules->Loans) && !empty($schedules->Loans) ? ($schedules->Loans->Customers ? $schedules->Loans->Customers->phone_number : '') : '' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-md-3 col-lg-3 mt-3">
                                            <div class="detail-group">
                                                <h6><b>{{ __('Loan') }}</b></h6>
                                                <p class="mb-20">
                                                    {{ !empty($schedules->Loans) ? loanPrefix() . $schedules->Loans->loan_id : '' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-lg-3 mt-3">
                                            <div class="detail-group">
                                                <h6><b>{{ __('Loan Type') }}</b></h6>
                                                <p class="mb-20">
                                                    {{ !empty($schedules->Loans) && !empty($schedules->Loans) ? ($schedules->Loans->loanType ? $schedules->Loans->loanType->type : '') : '' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-lg-3 mt-3">
                                            <div class="detail-group">
                                                <h6><b>{{ __('Amount') }}</b></h6>
                                                <p class="mb-20">
                                                    {{ !empty($schedules->Loans) ? priceFormat($schedules->Loans->amount) : '' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-lg-3 mt-3">
                                            <div class="detail-group">
                                                <h6><b>{{ __('Due Date') }}</b></h6>
                                                <p class="mb-20">
                                                    {{ dateFormat($schedules->due_date) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-lg-3 mt-3">
                                            <div class="detail-group">
                                                <h6><b>{{ __('Principal amount') }}</b></h6>
                                                <p class="mb-20">
                                                    {{ priceFormat($schedules->installment_amount) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-lg-3 mt-3">
                                            <div class="detail-group">
                                                <h6><b>{{ __('Interest') }}</b></h6>
                                                <p class="mb-20">
                                                    {{ priceFormat($schedules->interest) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-lg-3 mt-3">
                                            <div class="detail-group">
                                                <h6><b>{{ __('Penality') }}</b></h6>
                                                <p class="mb-20">
                                                    {{ $schedules->penality ? priceFormat($schedules->penality) : 0.0 }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-lg-3 mt-3">
                                            <div class="detail-group">
                                                <h6><b>{{ __('Total Amount') }}</b></h6>
                                                <p class="mb-20">
                                                    {{ priceFormat($schedules->total_amount) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if (\Auth::user()->type == 'customer')
                    @if (!in_array($schedules->status, ['Paid', 'In Process']) && Gate::check('repayment schedule payment'))
                        <div class="mt-25 col-lg-4 col-md-12" id="paymentModal" style="">
                            <div class="card">

                                <div class="col-xxl-12 cdx-xxl-100">
                                    <div class="payment-method">
                                        <div class="card-header">
                                            <h5> {{ __('Add Payment') }} </h5>
                                        </div>
                                        <div class="card-body">
                                            <ul class="nav nav-tabs profile-tabs border-bottom mb-3 d-print-none"
                                                id="myTab" role="tablist">
                                                @if ($settings['bank_transfer_payment'] == 'on')
                                                    <li class="nav-item">
                                                        <a class="nav-link text-sm active" id="profile-tab-1"
                                                            data-bs-toggle="tab" href="#bank_transfer" role="tab"
                                                            aria-selected="true">{{ __('Bank Transfer') }} </a>

                                                    </li>
                                                @endif

                                                @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
                                                    <li class="nav-item">

                                                        <a class="nav-link text-sm" id="profile-tab-2" data-bs-toggle="tab"
                                                            href="#stripe_payment" role="tab"
                                                            aria-selected="true">{{ __('Stripe') }}</a>
                                                    </li>
                                                @endif


                                                @if (
                                                    $settings['paypal_payment'] == 'on' &&
                                                        !empty($settings['paypal_client_id']) &&
                                                        !empty($settings['paypal_secret_key']))
                                                    <li class="nav-item">
                                                        <a class="nav-link text-sm" id="profile-tab-3" data-bs-toggle="tab"
                                                            href="#paypal_payment" role="tab" aria-selected="true">
                                                            {{ __('Paypal') }} </a>
                                                    </li>
                                                @endif

                                                @if (
                                                    $settings['flutterwave_payment'] == 'on' &&
                                                        !empty($settings['flutterwave_public_key']) &&
                                                        !empty($settings['flutterwave_secret_key']))
                                                    <li class="nav-item">
                                                        <a class="nav-link text-sm" id="profile-tab-3"
                                                            data-bs-toggle="tab" href="#flutterwave_payment"
                                                            role="tab" aria-selected="true">
                                                            {{ __('Flutterwave') }}
                                                        </a>
                                                    </li>
                                                @endif

                                                @if (
                                                    $settings['paystack_payment'] == 'on' &&
                                                        !empty($settings['paystack_public_key']) &&
                                                        !empty($settings['paystack_secret_key']))
                                                    <li class="nav-item">
                                                        <a class="nav-link text-sm" id="profile-tab-3"
                                                            data-bs-toggle="tab" href="#paystack_payment" role="tab"
                                                            aria-selected="true">
                                                            {{ __('Paystack') }}
                                                        </a>
                                                    </li>
                                                @endif


                                            </ul>

                                            <div class="tab-content">
                                                @if ($settings['bank_transfer_payment'] == 'on')
                                                    <div class="tab-pane fade active show" id="bank_transfer">
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <div class=" profile-user-box">
                                                                    <form
                                                                        action="{{ route('invoice.banktransfer.payment', \Illuminate\Support\Facades\Crypt::encrypt($schedules->id)) }}"
                                                                        method="post" class="require-validation"
                                                                        id="bank-payment" enctype="multipart/form-data">
                                                                        @csrf
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label for="card-name-on"
                                                                                        class="f-w-600 mb-1 text-start">{{ __('Bank Name') }}</label>
                                                                                    <p>{{ $settings['bank_name'] }}</p>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label for="card-name-on"
                                                                                        class="f-w-600 mb-1 text-start">{{ __('Bank Holder Name') }}</label>
                                                                                    <p>{{ $settings['bank_holder_name'] }}
                                                                                    </p>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label for="card-name-on"
                                                                                        class="f-w-600 mb-1 text-start">{{ __('Bank Account Number') }}</label>
                                                                                    <p>{{ $settings['bank_account_number'] }}
                                                                                    </p>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label for="card-name-on"
                                                                                        class="f-w-600 mb-1 text-start">{{ __('Bank IFSC Code') }}</label>
                                                                                    <p>{{ $settings['bank_ifsc_code'] }}
                                                                                    </p>
                                                                                </div>
                                                                            </div>
                                                                            @if (!empty($settings['bank_other_details']))
                                                                                <div class="col-md-12">
                                                                                    <div class="form-group">
                                                                                        <label for="card-name-on"
                                                                                            class="f-w-600 mb-1 text-start">{{ __('Bank Other Details') }}</label>
                                                                                        <p>{{ $settings['bank_other_details'] }}
                                                                                        </p>
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                            <div class="col-md-12">
                                                                                <div class="form-group">
                                                                                    <label for="amount"
                                                                                        class="form-label text-dark">{{ __('Amount') }}</label>
                                                                                    <input type="number" name="amount"
                                                                                        class="form-control required"
                                                                                        value="{{ $schedules->total_amount }}"
                                                                                        placeholder="{{ __('Enter Amount') }}"
                                                                                        required readonly>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <div class="form-group">
                                                                                    <label for="card-name-on"
                                                                                        class="form-label text-dark">{{ __('Attachment') }}</label>
                                                                                    <input type="file" name="receipt"
                                                                                        id="receipt"
                                                                                        class="form-control" required>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <div class="form-group">
                                                                                    <label for="notes"
                                                                                        class="form-label text-dark">{{ __('Notes') }}</label>
                                                                                    <input type="text" name="notes"
                                                                                        class="form-control "
                                                                                        value=""
                                                                                        placeholder="{{ __('Enter notes') }}">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-12 ">
                                                                                <input type="submit"
                                                                                    value="{{ __('Pay') }}"
                                                                                    class="btn btn-secondary">
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
                                                    <div class="tab-pane fade " id="stripe_payment">
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <div class=" profile-user-box">
                                                                    <form
                                                                        action="{{ route('invoice.stripe.payment', \Illuminate\Support\Facades\Crypt::encrypt($schedules->id)) }}"
                                                                        method="post" class="require-validation"
                                                                        id="stripe-payment">
                                                                        @csrf
                                                                        <div class="row">
                                                                            <div class="col-md-12">
                                                                                <div class="form-group">
                                                                                    <label for="amount"
                                                                                        class="form-label text-dark">{{ __('Amount') }}</label>
                                                                                    <input type="number" name="amount"
                                                                                        class="form-control required"
                                                                                        value="{{ $schedules->total_amount }}"
                                                                                        placeholder="{{ __('Enter Amount') }}"
                                                                                        required readonly>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <div class="form-group">
                                                                                    <label for="card-name-on"
                                                                                        class="form-label text-dark">{{ __('Card Name') }}</label>
                                                                                    <input type="text" name="name"
                                                                                        id="card-name-on"
                                                                                        class="form-control required"
                                                                                        placeholder="{{ __('Card Holder Name') }}">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-12">
                                                                                <label for="card-name-on"
                                                                                    class="form-label text-dark">{{ __('Card Details') }}</label>
                                                                                <div id="card-element">
                                                                                </div>
                                                                                <div id="stripe_card_errors"
                                                                                    role="alert">
                                                                                </div>
                                                                            </div>


                                                                            <div class="col-sm-12 mt-3">

                                                                                <input type="submit"
                                                                                    value="{{ __('Pay Now') }}"
                                                                                    class="btn btn-secondary">
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (
                                                    $settings['paypal_payment'] == 'on' &&
                                                        !empty($settings['paypal_client_id']) &&
                                                        !empty($settings['paypal_secret_key']))
                                                    <div class="tab-pane fade" id="paypal_payment">
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <div class=" profile-user-box">
                                                                    <form
                                                                        action="{{ route('invoice.paypal', \Illuminate\Support\Facades\Crypt::encrypt($schedules->id)) }}"
                                                                        method="post" class="require-validation">
                                                                        @csrf
                                                                        <div class="row">
                                                                            <div class="col-md-12">
                                                                                <div class="form-group">
                                                                                    <label for="amount"
                                                                                        class="form-label text-dark">{{ __('Amount') }}</label>
                                                                                    <input type="number" name="amount"
                                                                                        class="form-control required"
                                                                                        value="{{ $schedules->total_amount }}"
                                                                                        placeholder="{{ __('Enter Amount') }}"
                                                                                        required readonly>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-12 ">
                                                                                <input type="submit"
                                                                                    value="{{ __('Pay Now') }}"
                                                                                    class="btn btn-secondary">
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (
                                                    $settings['flutterwave_payment'] == 'on' &&
                                                        !empty($settings['flutterwave_public_key']) &&
                                                        !empty($settings['flutterwave_secret_key']))
                                                    <div class="tab-pane fade" id="flutterwave_payment">
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <div class=" profile-user-box">
                                                                    <form action="#" method="post"
                                                                        class="require-validation"
                                                                        id="flutterwavePaymentForm">
                                                                        @csrf
                                                                        <div class="row">
                                                                            <div class="col-md-12">
                                                                                <div class="form-group">

                                                                                    <label for="amount"
                                                                                        class="form-label text-dark">{{ __('Amount') }}</label>
                                                                                    <input type="number" name="amount"
                                                                                        class="form-control amount required"
                                                                                        value="{{ $schedules->total_amount }}"
                                                                                        placeholder="{{ __('Enter Amount') }}"
                                                                                        required readonly>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-12 ">
                                                                                <input type="button"
                                                                                    value="{{ __('Pay Now') }}"
                                                                                    class="btn btn-secondary"
                                                                                    id="flutterwavePaymentBtn">
                                                                            </div>

                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (
                                                    $settings['paystack_payment'] == 'on' &&
                                                        !empty($settings['paystack_public_key']) &&
                                                        !empty($settings['paystack_secret_key']))
                                                    <div class="tab-pane fade" id="paystack_payment">
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <div class=" profile-user-box">
                                                                    <form class="require-validation" method="POST"
                                                                        id="paystack-payment-form"
                                                                        action="{{ route('invoice.paystack.payment', \Illuminate\Support\Facades\Crypt::encrypt($schedules->id)) }}">

                                                                        @csrf
                                                                        <div class="row">
                                                                            <div class="col-md-12">
                                                                                <div class="form-group">

                                                                                    <label for="amount"
                                                                                        class="form-label text-dark">{{ __('Amount') }}</label>
                                                                                    <input type="number" name="amount"
                                                                                        class="form-control amount required"
                                                                                        value="{{ $schedules->total_amount }}"
                                                                                        placeholder="{{ __('Enter Amount') }}"
                                                                                        required readonly>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-12 ">
                                                                                <input type="button"
                                                                                    value="{{ __('Pay Now') }}"
                                                                                    class="btn btn-secondary"
                                                                                    id="paystackPaymentBtn">
                                                                            </div>

                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

            </div>


        </div>



    </div>
@endsection

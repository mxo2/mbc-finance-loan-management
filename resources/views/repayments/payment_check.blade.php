<div class="modal-body">
    <div class="row">

        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <h6><b>{{ __('Due date') }}</b></h6>
                <p class="mb-20">
                    {{ dateFormat($schedules->due_date) }}
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <h6><b>{{ __('Amount') }}</b></h6>
                <p class="mb-20">
                    {{ priceFormat($schedules->total_amount) }}
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <h6><b>{{ __('Transaction') }}</b></h6>
                <p class="mb-20">
                    {{ $schedules->transaction_id }}
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-6">
            <div class="detail-group">
                <h6><b>{{ __('Attachment') }}</b></h6>
                <p class="mb-20">
                    <a class="dropdown-item"
                        href="{{ !empty($schedules->receipt) ? fetch_file($schedules->receipt, 'upload/receipt/') : '#' }}"
                        target="_blank"><i data-feather="download"></i>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <a class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Make Payemnt') }}"
    href="{{ route('schedule.payment.status', ['id' => $schedules->id, 'status' => 'Accept']) }}">
    {{ __('Accept') }}
</a>

<a class="btn btn-danger" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Make Payemnt') }}"
    href="{{ route('schedule.payment.status', ['id' => $schedules->id, 'status' => 'Reject']) }}">
    {{ __('Reject') }}
</a>


</div>

{{Form::model($loanType, array('route' => array('loan-type.update', encrypt($loanType->id)), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group  col-md-12">
            {{Form::label('type',__('Type'),array('class'=>'form-label'))}}
            {{Form::text('type',null,array('class'=>'form-control','placeholder'=>__('Enter loan type name'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('loan_term_period', __('Loan Term Period'),['class'=>'form-label']) }}
            {!! Form::select('loan_term_period', $termPeroid, null,array('class' => 'form-control select2','required'=>'required')) !!}
        </div>
        <div class="form-group  col-md-6 col-lg-6">
            {{Form::label('max_loan_term',__('Loan Term Limit'),array('class'=>'form-label'))  }}
            {{Form::number('max_loan_term',null,array('class'=>'form-control','placeholder'=>__('Enter max term limit'),'required'=>'required'))}}
        </div>
        <div class="form-group  col-md-6 col-lg-6">
            {{Form::label('min_loan_amount',__('Minimum Loan Amount'),array('class'=>'form-label'))  }}
            {{Form::number('min_loan_amount',null,array('class'=>'form-control','placeholder'=>__('Enter min loan amount'),'required'=>'required'))}}
        </div>
        <div class="form-group  col-md-6 col-lg-6">
            {{Form::label('max_loan_amount',__('Maximum Loan Amount'),array('class'=>'form-label'))  }}
            {{Form::number('max_loan_amount',null,array('class'=>'form-control','placeholder'=>__('Enter max loan amount'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('interest_type', __('Interest Type'),['class'=>'form-label']) }}
            {!! Form::select('interest_type', $interestType, null,array('class' => 'form-control select2','required'=>'required')) !!}
        </div>
        <div class="form-group  col-md-6 col-lg-6">
            {{Form::label('interest_rate',__('Interest Rate (%) (per year)'),array('class'=>'form-label'))  }}
            {{Form::number('interest_rate',null,array('class'=>'form-control','placeholder'=>__('Enter interest rate'),'required'=>'required'))}}
        </div>
        <div class="form-group  col-md-6 col-lg-6">
            {{Form::label('penalties',__('Penalty (%)'),array('class'=>'form-label'))  }}
            {{Form::number('penalties',null,array('class'=>'form-control','placeholder'=>__('Enter payment penalty'),'required'=>'required'))}}
        </div>
        <div class="form-group  col-md-6 col-lg-6">
            {{Form::label('notes',__('Notes'),array('class'=>'form-label'))}}
            {{Form::textarea('notes',null,array('class'=>'form-control','placeholder'=>__('Enter notes'),'rows'=>1))}}
        </div>
        
        <!-- Payment Schedule Section -->
        <div class="col-md-12">
            <h6 class="mt-3 mb-2">{{ __('Payment Schedule Configuration') }}</h6>
        </div>
        
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('payment_frequency', __('Payment Frequency'),['class'=>'form-label']) }}
            {!! Form::select('payment_frequency', \App\Models\LoanType::$paymentFrequency, null,array('class' => 'form-control select2','required'=>'required', 'id' => 'payment_frequency')) !!}
        </div>
        
        <div class="form-group col-md-6 col-lg-6">
            {{Form::label('payment_day',__('Payment Day'),array('class'=>'form-label'))}}
            {{Form::number('payment_day',null,array('class'=>'form-control','placeholder'=>__('Enter payment day'),'required'=>'required', 'min' => '1', 'max' => '31', 'id' => 'payment_day'))}}
            <small class="text-muted" id="payment-day-help">{{ __('For monthly: 1-31 (day of month), For weekly: 1-7 (1=Monday, 7=Sunday)') }}</small>
        </div>
        
        <div class="form-group col-md-6 col-lg-6">
            <div class="form-check form-switch mt-4">
                {{ Form::checkbox('auto_start_date', 1, null, ['class' => 'form-check-input', 'id' => 'auto_start_date']) }}
                {{ Form::label('auto_start_date', __('Auto-set start date on approval'), ['class' => 'form-check-label']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}


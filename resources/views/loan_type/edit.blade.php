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
        <div class="form-group col-md-6 col-lg-6">
            {{Form::label('penalty_type',__('Penalty Type'),array('class'=>'form-label'))}}
            {!! Form::select('penalty_type', \App\Models\LoanType::$penaltyType, null,array('class' => 'form-control select2','required'=>'required', 'id' => 'penalty_type')) !!}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{Form::label('penalties',__('Penalty Amount'),array('class'=>'form-label', 'id' => 'penalty_label'))}}
            {{Form::number('penalties',null,array('class'=>'form-control','placeholder'=>__('Enter penalty amount'),'required'=>'required', 'step'=>'any', 'id' => 'penalty_amount'))}}
            <small class="text-muted" id="penalty_hint">Enter penalty amount</small>
        </div>
        
        <!-- File Charges Section -->
        <div class="col-md-12">
            <h6 class="mt-3 mb-2">{{ __('File Charges Configuration') }}</h6>
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{Form::label('file_charges_type',__('File Charges Type'),array('class'=>'form-label'))}}
            {!! Form::select('file_charges_type', \App\Models\LoanType::$fileChargesType, null,array('class' => 'form-control select2','required'=>'required', 'id' => 'file_charges_type_edit')) !!}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{Form::label('file_charges',__('File Charges Amount'),array('class'=>'form-label', 'id' => 'file_charges_label_edit'))}}
            {{Form::number('file_charges',null,array('class'=>'form-control','placeholder'=>__('Enter file charges amount'),'required'=>'required', 'step'=>'any', 'id' => 'file_charges_amount_edit'))}}
            <small class="text-muted" id="file_charges_hint_edit">Enter file charges amount</small>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const penaltyType = document.getElementById('penalty_type');
    const penaltyLabel = document.getElementById('penalty_label');
    const penaltyAmount = document.getElementById('penalty_amount');
    const penaltyHint = document.getElementById('penalty_hint');
    
    const fileChargesType = document.getElementById('file_charges_type_edit');
    const fileChargesLabel = document.getElementById('file_charges_label_edit');
    const fileChargesAmount = document.getElementById('file_charges_amount_edit');
    const fileChargesHint = document.getElementById('file_charges_hint_edit');
    
    function updatePenaltyFields() {
        if (penaltyType.value === 'percentage') {
            penaltyLabel.textContent = 'Penalty (%)';
            penaltyAmount.placeholder = 'Enter penalty percentage';
            penaltyHint.textContent = 'Enter percentage (e.g., 5 for 5%)';
        } else {
            penaltyLabel.textContent = 'Penalty (Fixed Amount)';
            penaltyAmount.placeholder = 'Enter penalty amount';
            penaltyHint.textContent = 'Enter fixed penalty amount (e.g., 100 for ₹100)';
        }
    }
    
    function updateFileChargesFields() {
        if (fileChargesType.value === 'percentage') {
            fileChargesLabel.textContent = 'File Charges (%)';
            fileChargesAmount.placeholder = 'Enter file charges percentage';
            fileChargesHint.textContent = 'Enter percentage (e.g., 2 for 2%)';
        } else {
            fileChargesLabel.textContent = 'File Charges (Fixed Amount)';
            fileChargesAmount.placeholder = 'Enter file charges amount';
            fileChargesHint.textContent = 'Enter fixed file charges amount (e.g., 500 for ₹500)';
        }
    }
    
    penaltyType.addEventListener('change', updatePenaltyFields);
    fileChargesType.addEventListener('change', updateFileChargesFields);
    
    updatePenaltyFields(); // Initialize on page load
    updateFileChargesFields(); // Initialize on page load
});
</script>


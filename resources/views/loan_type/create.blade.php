{{Form::open(array('url'=>'loan-type','method'=>'post'))}}
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
            {{Form::number('interest_rate',null,array('class'=>'form-control','placeholder'=>__('Enter interest rate'),'required'=>'required','step'=>'any'))}}
        </div>
        <div class="form-group  col-md-6 col-lg-6">
            {{Form::label('penalties',__('Penalty (%)'),array('class'=>'form-label'))  }}
            {{Form::number('penalties',null,array('class'=>'form-control','placeholder'=>__('Enter payment penalty'),'required'=>'required'))}}
        </div>
        <div class="form-group  col-md-6 col-lg-6">
            {{Form::label('notes',__('Notes'),array('class'=>'form-label'))}}
            {{Form::textarea('notes',null,array('class'=>'form-control','placeholder'=>__('Enter notes'),'rows'=>1))}}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{Form::submit(__('Create'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}



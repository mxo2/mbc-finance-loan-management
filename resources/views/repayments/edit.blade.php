{{Form::model($repayment, array('route' => array('repayment.update', encrypt($repayment->id)), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('loan_id', __('loan'), ['class' => 'form-label']) }}
            {!! Form::select('loan_id', $loans, null, [
                'class' => 'form-control select2 ',
                'required' => 'required',
            ]) !!}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('payment_date', __('payment date'), ['class' => 'form-label']) }}
            {{ Form::date('payment_date', null, ['class' => 'form-control', 'placeholder' => __('Enter payment date'), 'readonly']) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('principal_amount', __('principal amount'), ['class' => 'form-label']) }}
            {{ Form::number('principal_amount', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter principal amount'), 'readonly']) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('interest', __('interest'), ['class' => 'form-label']) }}
            {{ Form::number('interest', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter interest'), 'readonly']) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('penality', __('penality'), ['class' => 'form-label']) }}
            {{ Form::number('penality', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter penality'), 'readonly']) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('total_amount', __('total amount'), ['class' => 'form-label']) }}
            {{ Form::number('total_amount', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter total amount'), 'readonly']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}


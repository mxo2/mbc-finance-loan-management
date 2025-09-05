{{ Form::open(['url' => 'account-type', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group  col-md-12">
            {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter title')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('interest_rate', __('Interest Rate'), ['class' => 'form-label']) }}
            {{ Form::number('interest_rate', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter Interest Rate')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('interest_duration', __('Interest Duration'), ['class' => 'form-label']) }}
            {!! Form::select('interest_duration', $termPeroid, null, [
                'class' => 'form-control select2',
                'required' => 'required',
            ]) !!}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('min_maintain_amount', __('minimum maintain amount'), ['class' => 'form-label']) }}
            {{ Form::number('min_maintain_amount', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter Minimum Maintain Amount')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('maintenance_charges', __('Maintenance Charges'), ['class' => 'form-label']) }}
            {{ Form::number('maintenance_charges', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter Maintenance Charges')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('charges_deduct_month', __('Charges Deduct Month'), ['class' => 'form-label']) }}
            {{ Form::number('charges_deduct_month', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter Charges Deduct Month')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}

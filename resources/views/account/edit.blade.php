{{Form::model($account, array('route' => array('account.update', encrypt($account->id)), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('customer', __('Customer'), ['class' => 'form-label']) }}
            {!! Form::select('customer', $customers, null, [
                'class' => 'form-control select2',
                'required' => 'required',
            ]) !!}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('account_type', __('Account Type'), ['class' => 'form-label']) }}
            {!! Form::select('account_type', $type, null, [
                'class' => 'form-control select2',
                'required' => 'required',
            ]) !!}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {!! Form::select('status', $status, null, [
                'class' => 'form-control select2',
                'required' => 'required',
            ]) !!}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('balance', __('balance'), ['class' => 'form-label']) }}
            {{ Form::number('balance', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter balance')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('notes', __('notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => '1', 'placeholder' => __('Enter notes')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}


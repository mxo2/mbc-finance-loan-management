{{Form::model($expense, array('route' => array('expense.update', encrypt($expense->id)), 'method' => 'PUT', 'enctype' => "multipart/form-data")) }}
<div class="modal-body">
    <div class="row">

        <div class="form-group  col-md-12">
            {{ Form::label('title', __('title'), ['class' => 'form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter title')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('date', __('date'), ['class' => 'form-label']) }}
            {{ Form::date('date', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter date')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('amount', __('amount'), ['class' => 'form-label']) }}
            {{ Form::number('amount', null, ['class' => 'form-control', 'step' => 0.1, 'placeholder' => __('Enter amount')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('attachment', __('Attachment'), ['class' => 'form-label']) }}
            {{ Form::file('attachment', ['class' => 'form-control']) }}
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


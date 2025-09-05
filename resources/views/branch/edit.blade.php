{{Form::model($branch, array('route' => array('branch.update', encrypt($branch->id)), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group  col-md-12">
            {{Form::label('name',__('Name'),array('class'=>'form-label'))}}
            {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter branch name')))}}
        </div>
        <div class="form-group  col-md-12">
            {{Form::label('email',__('Email'),array('class'=>'form-label'))}}
            {{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter branch email')))}}
        </div>
        <div class="form-group  col-md-12">
            {{Form::label('phone_number',__('Phone Number'),array('class'=>'form-label'))}}
            {{Form::text('phone_number',null,array('class'=>'form-control','placeholder'=>__('Enter branch phone number')))}}
        </div>
        <div class="form-group  col-md-12">
            {{Form::label('location',__('Location'),array('class'=>'form-label'))}}
            {{Form::textarea('location',null,array('class'=>'form-control','placeholder'=>__('Enter branch address'),'rows'=>2))}}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}


@extends('layouts.app')

@section('content')
{{--    {!! Form::open(['action' => 'controller@store', 'method' => 'POST']) !!}--}}
@csrf
<h3>Protocol</h3>
    <div class="form-group">
        {{Form::label('datum', 'Datum')}} <br>
    </div>

{{Form::submit('Vytorit', ['class'=>'btn btn-primary'])}}


@endsection

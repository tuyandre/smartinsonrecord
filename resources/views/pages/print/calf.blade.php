@extends('layouts.print')

@section('title', 'Calf '.$calf['tag'])
@section('heading', 'New Born Report Form')

@section('content')
  <div class="section-title">Newborn information</div>
  <ul class="list-group mb-2">
    <li class="list-group-item"><strong>Ear Tag Number:</strong>&nbsp;{{ $calf['tag'] }}</li>
    <li class="list-group-item"><strong>Name:</strong>&nbsp;{{ $calf['name'] }}</li>
    <li class="list-group-item"><strong>Sex:</strong>&nbsp;{{ $calf['sex'] }}</li>
    <li class="list-group-item"><strong>Date of Birth:</strong>&nbsp;{{ $calf['dob'] }}</li>
    <li class="list-group-item"><strong>Born as calf number:</strong>&nbsp;{{ $calf['no'] }}</li>
  </ul>

  <div class="section-title">Mother and sire</div>
  <ul class="list-group mb-2">
    <li class="list-group-item"><strong>Mother Ear Tag Number:</strong>&nbsp;{{ $motherTag }}</li>
    <li class="list-group-item"><strong>Mother name:</strong>&nbsp;{{ $mother->Name ?? '—' }}</li>
    <li class="list-group-item"><strong>Owner:</strong>&nbsp;{{ $mother->Owner_names ?? '—' }}</li>
    <li class="list-group-item"><strong>Sire (bull used):</strong>&nbsp;{{ $calf['sire'] }}</li>
  </ul>

  <div class="section-title">Other information</div>
  <ul class="list-group mb-0">
    <li class="list-group-item"><strong>Weight at birth:</strong>&nbsp;{{ $calf['weight'] }} kg</li>
    <li class="list-group-item"><strong>Born at time of:</strong>&nbsp;{{ $calf['birth_times'] }}</li>
    <li class="list-group-item"><strong>Observation:</strong>&nbsp;{{ $calf['observation'] }}</li>
    <li class="list-group-item"><strong>Disability description if any:</strong>&nbsp;{{ $calf['disability'] }}</li>
  </ul>
@endsection

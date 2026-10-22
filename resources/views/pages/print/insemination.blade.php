@extends('layouts.print')

@section('title', 'Insemination '.$tag)
@section('heading', 'Insemination Report Form')

@section('content')
  <div class="section-title">Cow or Heifer Information</div>
  <ul class="list-group mb-2">
    <li class="list-group-item"><strong>Ear Tag Number:</strong>&nbsp;{{ $tag }}</li>
    <li class="list-group-item"><strong>Name:</strong>&nbsp;{{ $cow->Name ?? '—' }}</li>
    <li class="list-group-item"><strong>Owner:</strong>&nbsp;{{ $cow->Owner_names ?? '—' }}</li>
    <li class="list-group-item"><strong>Location:</strong>&nbsp;{{ collect([$cow->District ?? null, $cow->Sector ?? null])->filter()->implode(', ') ?: '—' }}</li>
    <li class="list-group-item"><strong>Previous calving date:</strong>&nbsp;{{ $previousCalving }}</li>
  </ul>

  <div class="section-title">Insemination rounds</div>
  <table class="table table-bordered table-sm align-middle mb-2">
    <thead class="table-light">
      <tr><th>AI time</th><th>Heat date</th><th>Heat hours</th><th>Insemination date</th><th>Bull used (semen)</th><th>Observation</th></tr>
    </thead>
    <tbody>
      @foreach ($rounds as $r)
        <tr><td>{{ $r['mating'] === '—' ? $r['no'] : $r['mating'] }}</td><td>{{ $r['heat'] }}</td><td>{{ $r['hours'] }}</td><td>{{ $r['date'] }}</td><td>{{ $r['bull'] }}</td><td>{{ $r['obs'] }}</td></tr>
      @endforeach
    </tbody>
  </table>

  <div class="section-title">Veterinary information</div>
  <table class="table table-bordered table-sm align-middle mb-0">
    <thead class="table-light"><tr><th>AI time</th><th>Veterinary name</th><th>RCVD ID</th><th>Email</th></tr></thead>
    <tbody>
      @foreach ($rounds as $r)
        <tr><td>{{ $r['no'] }}</td><td>{{ $r['vet'] }}</td><td>{{ $r['rcvd'] }}</td><td>{{ $r['email'] }}</td></tr>
      @endforeach
    </tbody>
  </table>
@endsection

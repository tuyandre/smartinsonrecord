@extends('layouts.app')

@section('title', 'Cow '.$cow->Eartagno.' · SMART INSEMrecord')
@section('page_title', $cow->Name.' · '.$cow->Eartagno)
@section('page_sub', 'Cow details')
@section('actions')
  <a href="{{ route('records.cows') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
  @can('cows.edit')
    <a href="{{ route('cows.edit', $cow->Eartagno) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a>
  @endcan
@endsection

@section('content')
  @php
    $groups = [
      'Cow' => ['Name' => 'Name', 'Eartagno' => 'Ear tag', 'Gender' => 'Gender', 'Dob' => 'Date of birth', 'Number_Calving' => 'Number of calving'],
      'Breed' => ['Breed' => 'Breed', 'Cross_breed1' => 'Cross breed 1', 'Cross_breed2' => 'Cross breed 2', 'Breed_Percentage' => 'Breed %'],
      'Owner' => ['Owner_names' => 'Owner', 'National_Id' => 'National ID', 'Phone' => 'Phone'],
      'Location' => ['District' => 'District', 'Sector' => 'Sector', 'Cell' => 'Cell', 'Village' => 'Village'],
    ];
  @endphp

  <div class="row g-3 mb-3">
    @foreach ($groups as $title => $fields)
      <div class="col-12 col-lg-6">
        <div class="card h-100">
          <div class="card-header">{{ $title }}</div>
          <div class="card-body">
            <div class="row g-3">
              @foreach ($fields as $field => $label)
                <div class="col-6">
                  <div class="small text-muted">{{ $label }}</div>
                  <div class="fw-medium">{{ $cow->$field !== null && $cow->$field !== '' ? $cow->$field : '—' }}</div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="card mb-3">
    <div class="card-header">Inseminations</div>
    @if ($rounds)
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead><tr><th>Round</th><th>Date</th><th>Bull</th><th>Observation</th><th>Veterinary</th></tr></thead>
          <tbody>
            @foreach ($rounds as $r)
              <tr><td>{{ $r['no'] }}</td><td>{{ $r['date'] }}</td><td>{{ $r['bull'] ?: '—' }}</td><td>{{ $r['observation'] ?: '—' }}</td><td>{{ $r['vet'] ?: '—' }}</td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="card-body text-muted">No inseminations recorded.</div>
    @endif
  </div>

  <div class="card">
    <div class="card-header">Calves</div>
    @if ($calves)
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead><tr><th>Ear tag</th><th>Breed</th><th>DOB</th><th>Weight (kg)</th><th>Observation</th><th>Disability</th></tr></thead>
          <tbody>
            @foreach ($calves as $c)
              <tr><td>{{ $c['tag'] }}</td><td>{{ $c['breed'] ?: '—' }}</td><td>{{ $c['dob'] ?: '—' }}</td><td>{{ $c['weight'] }}</td><td>{{ $c['observation'] ?: '—' }}</td><td>{{ $c['disability'] ?: '—' }}</td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="card-body text-muted">No calves recorded.</div>
    @endif
  </div>
@endsection

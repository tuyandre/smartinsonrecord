@extends('layouts.app')

@section('title', 'Calf '.$calf['tag'].' · SMART INSEMrecord')
@section('page_title', 'Calf '.$calf['tag'])
@section('page_sub', 'Birth record · calf number '.$calf['no'].' of '.($mother->Name ?? $calf['mother']))
@section('actions')
  <a href="{{ route('records.calves') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
  @can('calves.edit')
    <a href="{{ route('calves.edit', [$calf['mother'], $calf['no']]) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a>
  @endcan
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        @foreach ([
          'Calf ear tag' => $calf['tag'],
          'Mother' => $calf['mother'].($mother ? ' · '.$mother->Name : ''),
          'Sire (bull used)' => $calf['sire'] ?: null,
          'Date of birth' => $calf['dob'] ?: null,
          'Weight at birth (kg)' => $calf['weight'],
          'Birth times' => $calf['birth_times'],
          'Observation' => $calf['observation'] ?: null,
          'Disability' => $calf['disability'] ?: null,
        ] as $label => $value)
          <div class="col-6 col-md-3">
            <div class="small text-muted">{{ $label }}</div>
            <div class="fw-medium">{{ $value !== null && $value !== '' ? $value : '—' }}</div>
          </div>
        @endforeach
      </div>

      @if ($animal && auth()->user()->can($animal['permission']))
        <hr>
        <a href="{{ route($animal['route'], $calf['tag']) }}" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-{{ $animal['type'] === 'bull' ? 'gender-male' : 'tag' }} me-1"></i>Open registered {{ $animal['type'] }} ({{ $animal['name'] }})
        </a>
      @endif
    </div>
  </div>
@endsection

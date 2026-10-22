@extends('layouts.app')

@section('title', 'Semen '.$semen->bull_eartag.' · SMART INSEMrecord')
@section('page_title', ($semen->bull?->Name ?? 'Bull').' · '.$semen->bull_eartag)
@section('page_sub', 'Semen information')
@section('actions')
  <a href="{{ route('semen.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
  @can('semen.edit')
    <a href="{{ route('semen.edit', $semen) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a>
  @endcan
@endsection

@section('content')
  <div class="card mb-3">
    <div class="card-header">Bull</div>
    <div class="card-body">
      <div class="row g-3">
        @foreach (['Eartag' => $semen->bull_eartag, 'Name' => $semen->bull?->Name, 'Breed' => $semen->bull?->Breed, 'Dob' => $semen->bull?->Dob,
                   'International Code' => $semen->international_code, 'AIC No' => $semen->aic_no, 'Bull No' => $semen->bull_no] as $label => $value)
          <div class="col-6 col-md-3">
            <div class="small text-muted">{{ $label }}</div>
            <div class="fw-medium">{{ $value !== null && $value !== '' ? $value : '—' }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Semen batches</div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead><tr><th>#</th><th>Registration No</th><th>Freezing Date</th><th>Semen Code</th></tr></thead>
        <tbody>
          @foreach ($semen->lots as $lot)
            <tr><td>{{ $loop->iteration }}</td><td>{{ $lot->registration_no }}</td><td>{{ $lot->freezing_date->format('d/m/Y') }}</td><td>{{ $lot->semen_code }}</td></tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection

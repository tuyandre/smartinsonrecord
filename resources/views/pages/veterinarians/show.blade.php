@extends('layouts.app')

@section('title', 'Veterinarian '.$vet->rcvd_id.' · SMART INSEMrecord')
@section('page_title', $vet->names)
@section('page_sub', 'RCVD '.$vet->rcvd_id)
@section('actions')
  <a href="{{ route('veterinarians.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to list</a>
  @can('veterinarians.edit')
    <a href="{{ route('veterinarians.edit', $vet) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a>
  @endcan
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        @foreach ([
          'RCVD ID' => $vet->rcvd_id,
          'National ID' => $vet->national_id,
          'Names' => $vet->names,
          'Date of birth' => $vet->date_of_birth?->format('Y-m-d'),
          'District' => $vet->district,
          'Sector' => $vet->sector,
        ] as $label => $value)
          <div class="col-6 col-md-4">
            <div class="small text-muted">{{ $label }}</div>
            <div class="fw-medium">{{ $value !== null && $value !== '' ? $value : '—' }}</div>
          </div>
        @endforeach
        <div class="col-6 col-md-4">
          <div class="small text-muted">Status</div>
          <span class="badge {{ $vet->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $vet->is_active ? 'Active' : 'Inactive' }}</span>
        </div>
        <div class="col-6 col-md-4">
          <div class="small text-muted">System account</div>
          <div class="fw-medium">{{ $account ? $account->email : 'No account yet' }}</div>
        </div>
        <div class="col-6 col-md-4">
          <div class="small text-muted">Added</div>
          <div class="fw-medium">{{ $vet->created_at?->format('Y-m-d') }}</div>
        </div>
      </div>
    </div>
  </div>
@endsection
